// Blog Admin JavaScript
document.addEventListener('DOMContentLoaded', function() {
    
    // Image Preview for Form
    const featuredImageInput = document.getElementById('featured_image');
    const imagePreview = document.getElementById('image-preview');
    
    if (featuredImageInput && imagePreview) {
        featuredImageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Delete Modal Functionality
    const deleteConfirmModal = document.getElementById('deleteConfirmModal');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
    
    // Handle delete button clicks
    document.querySelectorAll('.blog-action-delete').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const postId = this.getAttribute('data-post-id');
            const postTitle = this.getAttribute('data-post-title');
            
            console.log('Deleting post:', postId, postTitle); // Debug
            
            // Set modal content
            document.getElementById('deletePostTitle').textContent = postTitle;
            document.getElementById('deleteForm').action = `/admin/blog/${postId}`;
            
            // Show modal
            deleteConfirmModal.style.display = 'flex';
        });
    });
    
    // Close delete modal with Cancel button
    if (cancelDeleteBtn) {
        cancelDeleteBtn.addEventListener('click', () => {
            deleteConfirmModal.style.display = 'none';
        });
    }
    
    // Close modal when clicking outside
    window.addEventListener('click', (e) => {
        if (e.target === deleteConfirmModal) {
            deleteConfirmModal.style.display = 'none';
        }
    });
    
    // Toggle Publish Status (if implemented in future)
    const publishToggles = document.querySelectorAll('.publish-toggle');
    publishToggles.forEach(toggle => {
        toggle.addEventListener('change', function() {
            const postId = this.dataset.postId;
            const isPublished = this.checked;
            
            fetch(`/admin/blog/${postId}/toggle-publish`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    is_published: isPublished
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update status badge
                    const badge = document.querySelector(`[data-post-badge="${postId}"]`);
                    if (badge) {
                        badge.textContent = isPublished ? 'Published' : 'Draft';
                        badge.className = isPublished ? 
                            'blog-status-badge blog-status-published' : 
                            'blog-status-badge blog-status-draft';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.checked = !isPublished; // Revert toggle
            });
        });
    });
    
    // Content Editor Character Count
    const contentTextarea = document.getElementById('content');
    const charCount = document.getElementById('char-count');
    
    if (contentTextarea && charCount) {
        contentTextarea.addEventListener('input', function() {
            const count = this.value.length;
            charCount.textContent = `${count} characters`;
            
            if (count > 10000) {
                charCount.style.color = '#dc3545';
            } else if (count > 5000) {
                charCount.style.color = '#ffc107';
            } else {
                charCount.style.color = '#28a745';
            }
        });
        
        // Initial count
        if (contentTextarea.value) {
            contentTextarea.dispatchEvent(new Event('input'));
        }
    }
    
    // Slug Generation
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');
    
    if (titleInput && slugInput && !slugInput.value) {
        titleInput.addEventListener('blur', function() {
            if (!slugInput.value) {
                // Simple slug generation
                const slug = this.value
                    .toLowerCase()
                    .replace(/[^\w\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/--+/g, '-')
                    .trim();
                slugInput.value = slug;
            }
        });
    }
    
    // Form Validation
    const blogForm = document.querySelector('form');
    if (blogForm) {
        blogForm.addEventListener('submit', function(e) {
            const title = document.getElementById('title');
            const description = document.getElementById('description');
            const content = document.getElementById('content');
            
            let isValid = true;
            
            // Clear previous error states
            document.querySelectorAll('.form-error').forEach(el => el.remove());
            document.querySelectorAll('.form-control.is-invalid').forEach(el => {
                el.classList.remove('is-invalid');
            });
            
            // Title validation
            if (!title.value.trim()) {
                showError(title, 'Title is required');
                isValid = false;
            } else if (title.value.trim().length > 255) {
                showError(title, 'Title must be less than 255 characters');
                isValid = false;
            }
            
            // Description validation
            if (!description.value.trim()) {
                showError(description, 'Description is required');
                isValid = false;
            } else if (description.value.trim().length > 500) {
                showError(description, 'Description must be less than 500 characters');
                isValid = false;
            }
            
            // Content validation
            if (!content.value.trim()) {
                showError(content, 'Content is required');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    }
    
    function showError(inputElement, message) {
        inputElement.classList.add('is-invalid');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'form-error';
        errorDiv.style.color = '#dc3545';
        errorDiv.style.fontSize = '0.85rem';
        errorDiv.style.marginTop = '5px';
        errorDiv.textContent = message;
        inputElement.parentNode.appendChild(errorDiv);
    }
    
    // Debug: Log when file is loaded
    console.log('Blog admin JS loaded successfully');

    // Newsletter functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Newsletter checkbox toggle
        const sendToNewsletter = document.getElementById('send_to_newsletter');
        const newsletterOptions = document.getElementById('newsletter-options');
        
        if (sendToNewsletter && newsletterOptions) {
            sendToNewsletter.addEventListener('change', function() {
                newsletterOptions.style.display = this.checked ? 'block' : 'none';
                
                // If unchecking, also hide schedule datetime
                if (!this.checked) {
                    const scheduleDatetime = document.getElementById('schedule-datetime');
                    if (scheduleDatetime) {
                        scheduleDatetime.style.display = 'none';
                    }
                }
            });
            
            // Trigger on load if checked
            if (sendToNewsletter.checked) {
                newsletterOptions.style.display = 'block';
            }
        }
        
        // Newsletter send option toggle
        const sendOptionRadios = document.querySelectorAll('input[name="newsletter_send_option"]');
        const scheduleDatetime = document.getElementById('schedule-datetime');
        
        if (sendOptionRadios.length > 0 && scheduleDatetime) {
            sendOptionRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === 'schedule') {
                        scheduleDatetime.style.display = 'block';
                        
                        // Set min date to now
                        const datetimeInput = document.getElementById('newsletter_scheduled_at');
                        if (datetimeInput) {
                            const now = new Date();
                            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
                            datetimeInput.min = now.toISOString().slice(0, 16);
                            
                            // Set default to 1 hour from now if empty
                            if (!datetimeInput.value) {
                                const oneHourLater = new Date(now);
                                oneHourLater.setHours(oneHourLater.getHours() + 1);
                                datetimeInput.value = oneHourLater.toISOString().slice(0, 16);
                            }
                        }
                    } else {
                        scheduleDatetime.style.display = 'none';
                    }
                });
            });
            
            // Set initial state
            const selectedOption = document.querySelector('input[name="newsletter_send_option"]:checked');
            if (selectedOption && selectedOption.value === 'schedule') {
                scheduleDatetime.style.display = 'block';
            }
        }
        
        // Validate newsletter form
        const newsletterForm = document.querySelector('form');
        if (newsletterForm) {
            newsletterForm.addEventListener('submit', function(e) {
                const sendToNewsletter = document.getElementById('send_to_newsletter');
                const sendOption = document.querySelector('input[name="newsletter_send_option"]:checked');
                const scheduledAt = document.getElementById('newsletter_scheduled_at');
                
                if (sendToNewsletter && sendToNewsletter.checked) {
                    if (!sendOption) {
                        e.preventDefault();
                        alert('Please select a newsletter send option.');
                        return false;
                    }
                    
                    if (sendOption.value === 'schedule' && (!scheduledAt || !scheduledAt.value)) {
                        e.preventDefault();
                        alert('Please select a date and time for the scheduled newsletter.');
                        return false;
                    }
                    
                    if (sendOption.value === 'schedule' && scheduledAt.value) {
                        const scheduledDate = new Date(scheduledAt.value);
                        const now = new Date();
                        
                        if (scheduledDate <= now) {
                            e.preventDefault();
                            alert('Scheduled date must be in the future.');
                            return false;
                        }
                    }
                }
            });
        }
        
        // Auto-check send to newsletter if post is published
        const isPublished = document.getElementById('is_published');
        if (isPublished && sendToNewsletter) {
            isPublished.addEventListener('change', function() {
                if (this.checked && !sendToNewsletter.checked) {
                    // Suggest sending to newsletter when publishing
                    if (confirm('Would you like to send this post to newsletter subscribers?')) {
                        sendToNewsletter.checked = true;
                        sendToNewsletter.dispatchEvent(new Event('change'));
                    }
                }
            });
        }
    });
});