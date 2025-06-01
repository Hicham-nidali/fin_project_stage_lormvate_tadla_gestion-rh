// ========================================
// 🎯 MAIN.JS - JavaScript Principal CORRIGÉ
// 📅 Version 2024 - Menu Toggle Fonctionnel
// ========================================

document.addEventListener('DOMContentLoaded', function() {
    
    // ========================================
    // 🧭 SIDEBAR RESPONSIVE - VERSION CORRIGÉE
    // ========================================
    const menuToggle = document.getElementById('menu-toggle');
    const wrapper = document.getElementById('wrapper');
    
    if (menuToggle && wrapper) {
        // Variable pour tracker l'état de la sidebar
        let sidebarHidden = false;
        
        // Fonction pour toggle la sidebar
        const toggleSidebar = () => {
            sidebarHidden = !sidebarHidden;
            
            if (sidebarHidden) {
                wrapper.classList.add('toggled');
                console.log('Sidebar masquée');
            } else {
                wrapper.classList.remove('toggled');
                console.log('Sidebar affichée');
            }
        };
        
        // Event listener pour le bouton menu
        menuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleSidebar();
        });
        
        // Fermer sidebar sur mobile si clic en dehors (uniquement sur mobile)
        document.addEventListener('click', function(e) {
            if (window.innerWidth < 768 && 
                !sidebarHidden && 
                !e.target.closest('#sidebar-wrapper') && 
                !e.target.closest('#menu-toggle')) {
                toggleSidebar();
            }
        });
        
        // Gérer le redimensionnement
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768) {
                // Sur desktop, réinitialiser l'état
                sidebarHidden = false;
                wrapper.classList.remove('toggled');
            }
        });
    }
    
    // ========================================
    // 🎨 ANIMATIONS D'ENTRÉE
    // ========================================
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Observer les cartes pour animation d'entrée
    document.querySelectorAll('.card').forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
        observer.observe(card);
    });
    
    // ========================================
    // 📊 STATISTIQUES ANIMÉES
    // ========================================
    const animateCounters = () => {
        document.querySelectorAll('.metric-value').forEach(counter => {
            const target = parseInt(counter.textContent.replace(/[^\d]/g, ''));
            if (target && !counter.dataset.animated) {
                counter.dataset.animated = 'true';
                let current = 0;
                const increment = target / 50;
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        counter.textContent = target;
                        clearInterval(timer);
                    } else {
                        counter.textContent = Math.floor(current);
                    }
                }, 40);
            }
        });
    };
    
    // Démarrer animation des compteurs quand visible
    const counterObserver = new IntersectionObserver(animateCounters, observerOptions);
    document.querySelectorAll('.metric-value').forEach(counter => {
        counterObserver.observe(counter);
    });
    
    // ========================================
    // 🎯 RECHERCHE EN TEMPS RÉEL
    // ========================================
    const setupSearchFilter = (searchId, tableSelector) => {
        const searchInput = document.getElementById(searchId);
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const filter = this.value.toLowerCase();
                const rows = document.querySelectorAll(`${tableSelector} tbody tr`);
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    const shouldShow = text.includes(filter);
                    row.style.display = shouldShow ? '' : 'none';
                    
                    // Animation de filtrage
                    if (shouldShow) {
                        row.style.animation = 'fadeInUp 0.3s ease';
                    }
                });
            });
        }
    };
    
    // Activer recherche pour différentes pages
    setupSearchFilter('task-search', '.table');
    setupSearchFilter('request-search', '.table');
    setupSearchFilter('report-search', '.table');
    
    // ========================================
    // 🎪 NOTIFICATIONS TOAST
    // ========================================
    const showToast = (message, type = 'info') => {
        const toast = document.createElement('div');
        toast.className = `toast-notification toast-${type}`;
        toast.innerHTML = `
            <div class="toast-content">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                <span>${message}</span>
            </div>
            <button class="toast-close">&times;</button>
        `;
        
        // Styles inline pour le toast
        Object.assign(toast.style, {
            position: 'fixed',
            top: '20px',
            right: '20px',
            background: type === 'success' ? '#059669' : type === 'error' ? '#dc2626' : '#2563eb',
            color: 'white',
            padding: '16px 20px',
            borderRadius: '12px',
            boxShadow: '0 10px 25px rgba(0,0,0,0.1)',
            zIndex: '9999',
            display: 'flex',
            alignItems: 'center',
            gap: '12px',
            maxWidth: '400px',
            transform: 'translateX(400px)',
            transition: 'transform 0.3s ease'
        });
        
        document.body.appendChild(toast);
        
        // Animation d'entrée
        setTimeout(() => {
            toast.style.transform = 'translateX(0)';
        }, 100);
        
        // Auto-remove
        setTimeout(() => {
            toast.style.transform = 'translateX(400px)';
            setTimeout(() => toast.remove(), 300);
        }, 5000);
        
        // Close button
        toast.querySelector('.toast-close').addEventListener('click', () => {
            toast.style.transform = 'translateX(400px)';
            setTimeout(() => toast.remove(), 300);
        });
    };
    
    // ========================================
    // 📋 AMÉLIORATION DES FORMULAIRES
    // ========================================
    
    // Auto-resize des textareas
    document.querySelectorAll('textarea').forEach(textarea => {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    });
    
    // Validation visuelle en temps réel
    document.querySelectorAll('input[required], select[required]').forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value.trim() === '') {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
        
        input.addEventListener('input', function() {
            if (this.classList.contains('is-invalid') && this.value.trim() !== '') {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
    });
    
    // ========================================
    // 🎯 INTERACTIONS BOUTONS
    // ========================================
    
    // Effet ripple sur les boutons
    document.querySelectorAll('.btn').forEach(button => {
        button.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            Object.assign(ripple.style, {
                position: 'absolute',
                width: size + 'px',
                height: size + 'px',
                left: x + 'px',
                top: y + 'px',
                background: 'rgba(255, 255, 255, 0.5)',
                borderRadius: '50%',
                transform: 'scale(0)',
                animation: 'ripple 0.6s linear',
                pointerEvents: 'none'
            });
            
            this.style.position = 'relative';
            this.style.overflow = 'hidden';
            this.appendChild(ripple);
            
            setTimeout(() => ripple.remove(), 600);
        });
    });
    
    // ========================================
    // 📊 AMÉLIORATION DES TABLEAUX
    // ========================================
    
    // Tri des colonnes
    const makeSortable = (table) => {
        const headers = table.querySelectorAll('th');
        headers.forEach((header, index) => {
            if (header.textContent.trim()) {
                header.style.cursor = 'pointer';
                header.addEventListener('click', () => {
                    sortTable(table, index);
                    
                    // Mise à jour des indicateurs de tri
                    headers.forEach(h => h.classList.remove('sort-asc', 'sort-desc'));
                    header.classList.toggle('sort-asc');
                });
            }
        });
    };
    
    const sortTable = (table, column) => {
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.rows);
        
        rows.sort((a, b) => {
            const aText = a.cells[column].textContent.trim();
            const bText = b.cells[column].textContent.trim();
            
            // Essayer de comparer comme nombres
            const aNum = parseFloat(aText);
            const bNum = parseFloat(bText);
            
            if (!isNaN(aNum) && !isNaN(bNum)) {
                return aNum - bNum;
            }
            
            // Sinon, comparer comme texte
            return aText.localeCompare(bText);
        });
        
        rows.forEach(row => tbody.appendChild(row));
    };
    
    // Activer tri sur tous les tableaux
    document.querySelectorAll('.table').forEach(makeSortable);
    
    // ========================================
    // 📱 OPTIMISATIONS PWA
    // ========================================
    
    // Détection du mode hors ligne
    const updateOnlineStatus = () => {
        const indicator = document.querySelector('.status-indicator');
        if (indicator) {
            indicator.classList.toggle('online', navigator.onLine);
            indicator.classList.toggle('offline', !navigator.onLine);
        }
        
        if (!navigator.onLine) {
            showToast('Mode hors ligne activé', 'info');
        }
    };
    
    window.addEventListener('online', updateOnlineStatus);
    window.addEventListener('offline', updateOnlineStatus);
    updateOnlineStatus();
    
    // ========================================
    // 🎯 LAZY LOADING POUR LES IMAGES
    // ========================================
    const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });
    
    document.querySelectorAll('img[data-src]').forEach(img => {
        imageObserver.observe(img);
    });
    
    // ========================================
    // ⚡ HEARTBEAT POUR SESSION
    // ========================================
    
    // Heartbeat pour maintenir la session active
    setInterval(function() {
        fetch('/heartbeat', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin'
        }).catch(function() {
            console.log('Session expirée');
        });
    }, 300000); // Toutes les 5 minutes
});

// ========================================
// 🎨 ANIMATIONS CSS COMPLÉMENTAIRES
// ========================================
const additionalCSS = `
@keyframes ripple {
    to {
        transform: scale(4);
        opacity: 0;
    }
}

@keyframes rainbow {
    0% { filter: hue-rotate(0deg); }
    100% { filter: hue-rotate(360deg); }
}

.toast-notification {
    animation: slideInRight 0.3s ease;
}

@keyframes slideInRight {
    from { transform: translateX(100%); }
    to { transform: translateX(0); }
}

.sort-asc::after {
    content: ' ↑';
    opacity: 0.7;
}

.sort-desc::after {
    content: ' ↓';
    opacity: 0.7;
}

.lazy {
    filter: blur(5px);
    transition: filter 0.3s;
}

.is-valid {
    border-color: #059669 !important;
    box-shadow: 0 0 0 0.2rem rgba(5, 150, 105, 0.25) !important;
}

.is-invalid {
    border-color: #dc2626 !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 38, 38, 0.25) !important;
}
`;

// Injecter le CSS additionnel
const style = document.createElement('style');
style.textContent = additionalCSS;
document.head.appendChild(style);