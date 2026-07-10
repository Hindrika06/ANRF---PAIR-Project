/* ============================================================
   PRELOADER JAVASCRIPT
   ============================================================ */

(function() {
    var isReload = false;
    try {
        isReload = (window.performance && window.performance.getEntriesByType && window.performance.getEntriesByType('navigation')[0] && window.performance.getEntriesByType('navigation')[0].type === 'reload');
    } catch(e) {}
    
    var hasPlayed = sessionStorage.getItem('anrf_preloader_played');
    var loader = document.getElementById('anrf-preloader');
    
    if (hasPlayed && !isReload) {
        if (loader) {
            loader.style.display = 'none';
            loader.parentNode.removeChild(loader);
        }
    } else {
        sessionStorage.setItem('anrf_preloader_played', 'true');
        document.documentElement.style.overflow = 'hidden';
        document.body.style.overflow = 'hidden';
        
        setTimeout(function() {
            if (loader) {
                loader.style.opacity = '0';
                setTimeout(function() {
                    if (loader.parentNode) {
                        loader.parentNode.removeChild(loader);
                    }
                    document.documentElement.style.overflow = '';
                    document.body.style.overflow = '';
                }, 400);
            }
        }, 3100);
    }
})();
