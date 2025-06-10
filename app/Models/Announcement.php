<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'content', 'meeting_date', 'meeting_location', 
        'priority', 'status', 'created_by'
    ];

    protected $casts = [
        'meeting_date' => 'datetime',
    ];

    // Relations
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reads()
    {
        return $this->hasMany(AnnouncementRead::class);
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeUpcoming($query)
    {
        return $query->where('meeting_date', '>=', now());
    }

    // Accessors
    public function getPriorityLabelAttribute()
    {
        $labels = [
            'normal' => 'Normal',
            'high' => 'Priorité Élevée',
            'urgent' => 'Urgent'
        ];
        return $labels[$this->priority] ?? 'Normal';
    }

    public function getPriorityColorAttribute()
    {
        $colors = [
            'normal' => 'primary',
            'high' => 'warning',
            'urgent' => 'danger'
        ];
        return $colors[$this->priority] ?? 'primary';
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'draft' => 'Brouillon',
            'published' => 'Publié',
            'archived' => 'Archivé'
        ];
        return $labels[$this->status] ?? 'Brouillon';
    }

    // Methods
    public function isReadBy(User $user)
    {
        return $this->reads()->where('user_id', $user->id)->exists();
    }

    public function markAsReadBy(User $user)
    {
        if (!$this->isReadBy($user)) {
            $this->reads()->create(['user_id' => $user->id]);
        }
    }

    public function getReadCount()
    {
        return $this->reads()->count();
    }

    public function getTotalPotentialReaders()
    {
        return User::whereIn('role', ['employee', 'department_head', 'hr_admin'])->count();
    }

    public function getReadPercentage()
    {
        $total = $this->getTotalPotentialReaders();
        if ($total === 0) return 0;
        
        return round(($this->getReadCount() / $total) * 100, 1);
    }

    public function isUpcoming()
    {
        return $this->meeting_date && $this->meeting_date->isFuture();
    }

    public function isToday()
    {
        return $this->meeting_date && $this->meeting_date->isToday();
    }

    public function isTomorrow()
    {
        return $this->meeting_date && $this->meeting_date->isTomorrow();
    }

    public function getTimeUntilMeeting()
    {
        if (!$this->meeting_date) return null;
        
        return $this->meeting_date->diffForHumans();
    }

    public function canBeEdited()
    {
        return $this->status === 'draft';
    }

    public function canBePublished()
    {
        return $this->status === 'draft';
    }

    public function canBeArchived()
    {
        return $this->status === 'published';
    }

    // Static methods
    public static function getUnreadCountForUser(User $user)
    {
        return static::published()
                    ->whereDoesntHave('reads', function($query) use ($user) {
                        $query->where('user_id', $user->id);
                    })
                    ->count();
    }

    public static function getUrgentUnreadCountForUser(User $user)
    {
        return static::published()
                    ->where('priority', 'urgent')
                    ->whereDoesntHave('reads', function($query) use ($user) {
                        $query->where('user_id', $user->id);
                    })
                    ->count();
    }

    public static function getTodayMeetings()
    {
        return static::published()
                    ->whereDate('meeting_date', today())
                    ->orderBy('meeting_date')
                    ->get();
    }

    public static function getUpcomingMeetings($days = 7)
    {
        return static::published()
                    ->whereBetween('meeting_date', [now(), now()->addDays($days)])
                    ->orderBy('meeting_date')
                    ->get();
    }
}