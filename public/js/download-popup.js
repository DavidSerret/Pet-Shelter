document.addEventListener('DOMContentLoaded', function() {
    const popup = document.getElementById('downloadPopup');
    const closeBtn = document.getElementById('closePopup');
    const dontShowAgainCheckbox = document.getElementById('dontShowAgain');
    const downloadGuideBtn = document.getElementById('downloadGuideBtn');
    const downloadCodeBtn = document.getElementById('downloadCodeBtn');
    
    // Check if on homepage
    const isHomepage = window.location.pathname === '/' || 
                       window.location.pathname === '/home' || 
                       window.location.pathname === '';
    
    // Check if popup should be shown
    const shouldShowPopup = () => {
        if (!isHomepage) return false;
        
        const dontShowAgain = localStorage.getItem('downloadPopupDisabled');
        return !dontShowAgain;
    };
    
    // Show popup
    const showPopup = () => {
        if (popup) {
            popup.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    };
    
    // Hide popup
    const hidePopup = () => {
        if (popup) {
            popup.style.display = 'none';
            document.body.style.overflow = '';
        }
    };
    
    // Close popup when clicking X
    if (closeBtn) {
        closeBtn.addEventListener('click', hidePopup);
    }
    
    // Close popup when clicking outside
    if (popup) {
        popup.addEventListener('click', function(e) {
            if (e.target === this) {
                hidePopup();
            }
        });
    }
    
    // Handle "Don't show again" checkbox
    if (dontShowAgainCheckbox) {
        // Load checkbox state from localStorage
        const isDisabled = localStorage.getItem('downloadPopupDisabled');
        if (isDisabled) {
            dontShowAgainCheckbox.checked = true;
        }
        
        dontShowAgainCheckbox.addEventListener('change', function() {
            if (this.checked) {
                localStorage.setItem('downloadPopupDisabled', 'true');
            } else {
                localStorage.removeItem('downloadPopupDisabled');
            }
        });
    }
    
    // Track downloads and auto-check checkbox
    const trackDownload = (e) => {
        // Prevent default to ensure checkbox is checked before redirect
        e.preventDefault();
        
        // Mark checkbox and save to localStorage
        dontShowAgainCheckbox.checked = true;
        localStorage.setItem('downloadPopupDisabled', 'true');
        
        // Get the URL and open in new tab
        const url = e.currentTarget.href;
        window.open(url, '_blank');
        
        // Hide popup
        setTimeout(hidePopup, 300);
        
        // Return false to prevent default anchor behavior
        return false;
    };
    
    // Track when user downloads files
    if (downloadGuideBtn) {
        downloadGuideBtn.addEventListener('click', trackDownload);
    }
    
    if (downloadCodeBtn) {
        downloadCodeBtn.addEventListener('click', trackDownload);
    }
    
    // Alternative: Use mouseup for better tracking
    const trackDownloadAlternative = (e) => {
        // Only track left clicks
        if (e.button !== 0) return;
        
        dontShowAgainCheckbox.checked = true;
        localStorage.setItem('downloadPopupDisabled', 'true');
        
        // Small delay to ensure localStorage is saved
        setTimeout(() => {
            hidePopup();
        }, 100);
    };
    
    // Add mouseup listener as backup
    if (downloadGuideBtn) {
        downloadGuideBtn.addEventListener('mouseup', trackDownloadAlternative);
    }
    
    if (downloadCodeBtn) {
        downloadCodeBtn.addEventListener('mouseup', trackDownloadAlternative);
    }
    
    // Show popup on homepage load
    if (shouldShowPopup()) {
        // Small delay to let page load first
        setTimeout(showPopup, 1000);
    }
    
    // Handle Escape key to close popup
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && popup.style.display === 'flex') {
            hidePopup();
        }
    });
    
    // Optional: Add a button to manually show the popup
    const createPopupTrigger = () => {
        const triggerBtn = document.createElement('button');
        triggerBtn.innerHTML = '📥 Free Resources';
        triggerBtn.id = 'popupTriggerBtn';
        triggerBtn.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: linear-gradient(135deg, #AC5512, #8a4510);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 25px;
            cursor: pointer;
            z-index: 9998;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(172,85,18,0.3);
            transition: all 0.3s ease;
        `;
        
        triggerBtn.addEventListener('mouseenter', () => {
            triggerBtn.style.transform = 'translateY(-2px)';
            triggerBtn.style.boxShadow = '0 6px 20px rgba(172,85,18,0.4)';
        });
        
        triggerBtn.addEventListener('mouseleave', () => {
            triggerBtn.style.transform = 'translateY(0)';
            triggerBtn.style.boxShadow = '0 4px 15px rgba(172,85,18,0.3)';
        });
        
        triggerBtn.addEventListener('click', () => {
            // Temporarily enable popup
            localStorage.removeItem('downloadPopupDisabled');
            dontShowAgainCheckbox.checked = false;
            showPopup();
        });
        
        document.body.appendChild(triggerBtn);
    };
    
    // Add trigger button after a delay
    setTimeout(createPopupTrigger, 2000);
});