document.addEventListener('DOMContentLoaded', function() {
    console.log('Download popup script loaded');
    
    const popup = document.getElementById('downloadPopup');
    const closeBtn = document.getElementById('closePopup');
    const dontShowAgainCheckbox = document.getElementById('dontShowAgain');
    
    if (!popup) {
        console.error('Popup element not found!');
        return;
    }
    
    console.log('Popup element found:', popup);
    
    // Check if on homepage
    const isHomepage = window.location.pathname === '/' || 
                       window.location.pathname === '/home' || 
                       window.location.pathname === '';
    
    console.log('Is homepage?', isHomepage);
    
    // Check if popup should be shown
    const shouldShowPopup = () => {
        if (!isHomepage) {
            console.log('Not on homepage, not showing popup');
            return false;
        }
        
        const dontShowAgain = localStorage.getItem('downloadPopupDisabled');
        console.log('LocalStorage value:', dontShowAgain);
        return !dontShowAgain;
    };
    
    // Show popup
    const showPopup = () => {
        console.log('Showing popup');
        popup.style.display = 'flex';
        // document.body.style.overflow = 'hidden';
    };
    
    // Hide popup
    const hidePopup = () => {
        console.log('Hiding popup');
        popup.style.display = 'none';
        // document.body.style.overflow = '';
    };
    
    // Close popup when clicking X
    if (closeBtn) {
        closeBtn.addEventListener('click', hidePopup);
        console.log('Close button event listener added');
    }
    
    // Close popup when clicking outside
    popup.addEventListener('click', function(e) {
        if (e.target === this) {
            hidePopup();
        }
    });
    
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
                console.log('Popup disabled in localStorage');
            } else {
                localStorage.removeItem('downloadPopupDisabled');
                console.log('Popup enabled in localStorage');
            }
        });
    }
    
    // Track downloads
    const trackDownload = (event) => {
        console.log('Download clicked');
        
        // Mark checkbox
        if (dontShowAgainCheckbox) {
            dontShowAgainCheckbox.checked = true;
            localStorage.setItem('downloadPopupDisabled', 'true');
        }
        
        // Hide popup immediately
        hidePopup();
        
        // Allow default download behavior
        // No preventDefault, no window.open
        return true;
    };
    
    // Add download tracking
    const downloadGuideBtn = document.getElementById('downloadGuideBtn');
    const downloadCodeBtn = document.getElementById('downloadCodeBtn');
    
    if (downloadGuideBtn) {
        downloadGuideBtn.addEventListener('click', trackDownload);
        console.log('Guide download button listener added');
    }
    
    if (downloadCodeBtn) {
        downloadCodeBtn.addEventListener('click', trackDownload);
        console.log('Code download button listener added');
    }
    
    // Show popup on homepage load
    if (shouldShowPopup()) {
        console.log('Should show popup, scheduling...');
        // Small delay to let page load first
        setTimeout(showPopup, 1500);
    } else {
        console.log('Popup should NOT be shown');
    }
    
    // Handle Escape key to close popup
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && popup.style.display === 'flex') {
            hidePopup();
        }
    });
    
    // Create trigger button
    const createPopupTrigger = () => {
        // Check if button already exists
        if (document.getElementById('popupTriggerBtn')) return;
        
        const triggerBtn = document.createElement('button');
        triggerBtn.innerHTML = '📥 Free Resources';
        triggerBtn.id = 'popupTriggerBtn';
        triggerBtn.type = 'button';
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
            font-family: inherit;
        `;
        
        triggerBtn.addEventListener('mouseenter', () => {
            triggerBtn.style.transform = 'translateY(-2px)';
            triggerBtn.style.boxShadow = '0 6px 20px rgba(172,85,18,0.4)';
        });
        
        triggerBtn.addEventListener('mouseleave', () => {
            triggerBtn.style.transform = 'translateY(0)';
            triggerBtn.style.boxShadow = '0 4px 15px rgba(172,85,18,0.3)';
        });
        
        triggerBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            // Temporarily enable popup
            localStorage.removeItem('downloadPopupDisabled');
            if (dontShowAgainCheckbox) {
                dontShowAgainCheckbox.checked = false;
            }
            showPopup();
        });
        
        document.body.appendChild(triggerBtn);
        console.log('Trigger button created');
    };
    
    // Add trigger button after a delay
    setTimeout(createPopupTrigger, 3000);
    
    console.log('Download popup script initialized successfully');
});