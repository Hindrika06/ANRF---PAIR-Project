// Authentication utility for protected pages
class AuthManager {
    constructor() {
        this.user = null;
        this.isChecking = false;
    }

    // Check if user is authenticated
    async checkAuth() {
        if (this.isChecking) return;
        this.isChecking = true;

        try {
            const response = await fetch('backend/auth_check.php', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const data = await response.json();
                if (data.authenticated && data.user) {
                    this.user = data.user;
                    this.showAuthenticatedContent();
                    return true;
                }
            }
        } catch (error) {
            console.error('Auth check failed:', error);
        }

        this.isChecking = false;
        
        // Check if we're on the main page
        const isMainPage = window.location.pathname.includes('index.html') || 
                          window.location.pathname.endsWith('/') ||
                          window.location.pathname.endsWith('/templatemo_537_art_factory');
        
        // Only show login prompt on feature pages, not on main page
        if (!isMainPage) {
            this.showLoginPrompt();
        }
        
        return false;
    }

    // Show login prompt when user is not authenticated
    showLoginPrompt() {
        const body = document.body;
        
        // Check if we're on the main page (index.html)
        const isMainPage = window.location.pathname.includes('index.html') || 
                          window.location.pathname.endsWith('/') ||
                          window.location.pathname.endsWith('/templatemo_537_art_factory');
        
        // Create overlay
        const overlay = document.createElement('div');
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        `;

        // Create login prompt
        const prompt = document.createElement('div');
        prompt.style.cssText = `
            background: white;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            max-width: 400px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        `;

        if (isMainPage) {
            // If on main page, show login modal directly
            prompt.innerHTML = `
                <h2 style="color: #0ea5e9; margin-bottom: 20px;">🔐 Login Required</h2>
                <p style="color: #64748b; margin-bottom: 25px;">
                    You need to be logged in to access this feature.
                </p>
                <div style="display: flex; gap: 15px; justify-content: center;">
                    <button id="showLoginModal" style="
                        background: linear-gradient(90deg, #0ea5e9, #0284c7);
                        color: white;
                        border: none;
                        padding: 12px 24px;
                        border-radius: 8px;
                        cursor: pointer;
                        font-weight: 600;
                    ">Login Now</button>
                    <button id="closePrompt" style="
                        background: #f1f5f9;
                        color: #64748b;
                        border: 1px solid #e2e8f0;
                        padding: 12px 24px;
                        border-radius: 8px;
                        cursor: pointer;
                        font-weight: 600;
                    ">Close</button>
                </div>
            `;
        } else {
            // If on feature page, redirect to main page
            prompt.innerHTML = `
                <h2 style="color: #0ea5e9; margin-bottom: 20px;">🔐 Login Required</h2>
                <p style="color: #64748b; margin-bottom: 25px;">
                    You need to be logged in to access this feature.
                </p>
                <div style="display: flex; gap: 15px; justify-content: center;">
                    <button id="goToLogin" style="
                        background: linear-gradient(90deg, #0ea5e9, #0284c7);
                        color: white;
                        border: none;
                        padding: 12px 24px;
                        border-radius: 8px;
                        cursor: pointer;
                        font-weight: 600;
                    ">Go to Login</button>
                    <button id="goHome" style="
                        background: #f1f5f9;
                        color: #64748b;
                        border: 1px solid #e2e8f0;
                        padding: 12px 24px;
                        border-radius: 8px;
                        cursor: pointer;
                        font-weight: 600;
                    ">Go Home</button>
                </div>
            `;
        }

        overlay.appendChild(prompt);
        body.appendChild(overlay);

        // Event listeners based on page type
        if (isMainPage) {
            // On main page - show login modal
            document.getElementById('showLoginModal').addEventListener('click', () => {
                body.removeChild(overlay);
                // Trigger the login modal
                if (typeof $ !== 'undefined' && $('#loginModal').length) {
                    $('#loginModal').modal('show');
                } else {
                    // Fallback if jQuery/bootstrap not available
                    const loginModal = document.getElementById('loginModal');
                    if (loginModal) {
                        loginModal.style.display = 'block';
                        loginModal.classList.add('show');
                    }
                }
            });

            document.getElementById('closePrompt').addEventListener('click', () => {
                body.removeChild(overlay);
            });
        } else {
            // On feature page - redirect to main page
            document.getElementById('goToLogin').addEventListener('click', () => {
                window.location.href = 'index.html';
            });

            document.getElementById('goHome').addEventListener('click', () => {
                window.location.href = 'index.html';
            });
        }
    }

    // Show authenticated content (override in child classes)
    showAuthenticatedContent() {
        // This will be overridden by each page
        console.log('User authenticated:', this.user);
    }

    // Get current user
    getCurrentUser() {
        return this.user;
    }

    // Logout function
    async logout() {
        try {
            await fetch('backend/auth_logout.php');
            this.user = null;
            window.location.href = 'index.html';
        } catch (error) {
            console.error('Logout failed:', error);
        }
    }

    // Save data with user ID
    async saveData(endpoint, data) {
        if (!this.user) {
            throw new Error('User not authenticated');
        }

        const payload = {
            ...data,
            user_id: this.user.id
        };

        const response = await fetch(`backend/${endpoint}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        return response.json();
    }
}

// Global auth manager instance
window.authManager = new AuthManager();

// Auto-check authentication when page loads
document.addEventListener('DOMContentLoaded', () => {
    authManager.checkAuth();
});
