// Logout Modal Functions
function showLogoutModal() {
    const modal = document.getElementById('logout-modal');
    if (modal) {
        // Remove any existing show class first
        modal.classList.remove('show');
        
        // Show modal immediately
        modal.style.display = 'block';
        modal.style.backgroundColor = 'rgba(220, 53, 69, 0.1)';
        modal.style.backdropFilter = 'blur(5px)';
        modal.style.webkitBackdropFilter = 'blur(5px)';
        
        document.body.style.overflow = 'hidden';
        
        // Add show class after a brief delay for smooth animation
        requestAnimationFrame(() => {
            modal.classList.add('show');
        });
        
        // Add backdrop click handler (only once)
        const handleBackdropClick = function(e) {
            if (e.target === modal) {
                closeLogoutModal();
                modal.removeEventListener('click', handleBackdropClick);
            }
        };
        modal.addEventListener('click', handleBackdropClick);
        
    } else {
        // Fallback if modal doesn't exist
        if (confirm('Bạn có chắc chắn muốn đăng xuất?')) {
            confirmLogout();
        }
    }
}

function closeLogoutModal() {
    const modal = document.getElementById('logout-modal');
    if (modal) {
        // Remove show class for exit animation
        modal.classList.remove('show');
        
        // Hide modal after animation completes
        setTimeout(() => {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }, 300);
    }
}

function confirmLogout() {
    // Show loading state
    const confirmBtn = document.querySelector('.logout-confirm-btn');
    if (confirmBtn) {
        const originalText = confirmBtn.innerHTML;
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang đăng xuất...';
        confirmBtn.disabled = true;
    }
    
    // Show message if showMessage function exists
    if (typeof showMessage === 'function') {
        showMessage('Đang đăng xuất...', 'info');
    }
    
    // Redirect to logout
    setTimeout(() => {
        window.location.href = '/webapp/logout';
    }, 800);
}

// Prevent modal content clicks from closing modal
document.addEventListener('DOMContentLoaded', function() {
    // Close modal when pressing Escape
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const logoutModal = document.getElementById('logout-modal');
            if (logoutModal && logoutModal.style.display === 'block') {
                closeLogoutModal();
            }
        }
    });
    
    // Prevent modal content clicks from bubbling to backdrop
    const logoutModal = document.getElementById('logout-modal');
    if (logoutModal) {
        const modalContent = logoutModal.querySelector('.modal-content');
        if (modalContent) {
            modalContent.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
    }
});