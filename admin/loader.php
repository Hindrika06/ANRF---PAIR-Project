<!-- ==========================================
     LOADING ANIMATION SNIPPETS FOR EACH PAGE
     =========================================== -->

<!-- SNIPPET 1: Simple Spinner (Recommended - Lightweight) -->
<div class="page-loader" id="pageLoader">
    <div class="loader-spinner"></div>
    <p class="loader-text">Loading content...</p>
</div>

<style>
.page-loader {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 1000;
    text-align: center;
}

.page-loader.active {
    display: block;
}

.loader-spinner {
    width: 50px;
    height: 50px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #024283;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 15px;
}

.loader-text {
    color: #666;
    font-size: 16px;
    font-weight: 500;
    margin: 0;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<script>
// Page loader is disabled to make page transitions instant and smooth.
</script>

<!-- ==========================================
     SNIPPET 2: Animated Dots (Fun & Modern)
     =========================================== -->

<!--
<div class="page-loader" id="pageLoader">
    <div class="loader-dots">
        <span></span>
        <span></span>
        <span></span>
    </div>
    <p class="loader-text">Loading content...</p>
</div>

<style>
.page-loader {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 1000;
    text-align: center;
}

.page-loader.active {
    display: block;
}

.loader-dots {
    margin: 0 auto 15px;
}

.loader-dots span {
    display: inline-block;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background-color: #024283;
    margin: 0 6px;
    animation: bounce 1.4s infinite ease-in-out both;
}

.loader-dots span:nth-child(1) {
    animation-delay: -0.32s;
}

.loader-dots span:nth-child(2) {
    animation-delay: -0.16s;
}

@keyframes bounce {
    0%, 80%, 100% {
        transform: scale(0);
        opacity: 0.5;
    }
    40% {
        transform: scale(1);
        opacity: 1;
    }
}

.loader-text {
    color: #666;
    font-size: 16px;
    font-weight: 500;
    margin: 0;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const loader = document.getElementById('pageLoader');
    if (loader) loader.classList.add('active');
});

window.addEventListener('load', function() {
    const loader = document.getElementById('pageLoader');
    if (loader) {
        setTimeout(() => {
            loader.classList.remove('active');
        }, 300);
    }
});
</script>
-->

<!-- ==========================================
     SNIPPET 3: Pulse Loading (Elegant)
     =========================================== -->

<!--
<div class="page-loader" id="pageLoader">
    <div class="loader-pulse"></div>
    <p class="loader-text">Loading content...</p>
</div>

<style>
.page-loader {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 1000;
    text-align: center;
}

.page-loader.active {
    display: block;
}

.loader-pulse {
    width: 60px;
    height: 60px;
    margin: 0 auto 15px;
    background-color: #024283;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(2, 66, 131, 0.7);
    }
    70% {
        box-shadow: 0 0 0 20px rgba(2, 66, 131, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(2, 66, 131, 0);
    }
}

.loader-text {
    color: #666;
    font-size: 16px;
    font-weight: 500;
    margin: 0;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const loader = document.getElementById('pageLoader');
    if (loader) loader.classList.add('active');
});

window.addEventListener('load', function() {
    const loader = document.getElementById('pageLoader');
    if (loader) {
        setTimeout(() => {
            loader.classList.remove('active');
        }, 300);
    }
});
</script>
-->

<!-- ==========================================
     SNIPPET 4: Progress Bar (Modern)
     =========================================== -->

<!--
<div class="page-loader" id="pageLoader">
    <div class="loader-progress-bar"></div>
    <p class="loader-text">Loading content...</p>
</div>

<style>
.page-loader {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
}

.page-loader.active {
    display: block;
}

.loader-progress-bar {
    height: 4px;
    background: linear-gradient(90deg, #024283, #886cc0, #024283);
    background-size: 200% 100%;
    animation: progress 2s infinite;
}

@keyframes progress {
    0% {
        width: 0;
        background-position: 0% 0;
    }
    50% {
        width: 90%;
        background-position: 100% 0;
    }
    100% {
        width: 100%;
        background-position: 100% 0;
    }
}

.loader-text {
    text-align: center;
    color: #666;
    font-size: 14px;
    margin-top: 20px;
    display: none;
}

.loader-text.show {
    display: block;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const loader = document.getElementById('pageLoader');
    if (loader) loader.classList.add('active');
});

window.addEventListener('load', function() {
    const loader = document.getElementById('pageLoader');
    if (loader) {
        setTimeout(() => {
            loader.classList.remove('active');
        }, 500);
    }
});
</script>
-->

<!-- ==========================================
     SNIPPET 5: Skeleton Loading (Professional)
     =========================================== -->

<!--
<div class="page-loader" id="pageLoader">
    <div class="skeleton-container">
        <div class="skeleton-title"></div>
        <div class="skeleton-line"></div>
        <div class="skeleton-line"></div>
        <div class="skeleton-line" style="width: 80%;"></div>
        <div class="skeleton-line" style="margin-top: 20px;"></div>
        <div class="skeleton-line"></div>
    </div>
</div>

<style>
.page-loader {
    display: none;
    padding: 30px;
    z-index: 1000;
}

.page-loader.active {
    display: block;
}

.skeleton-container {
    max-width: 600px;
    margin: 0 auto;
}

.skeleton-title {
    height: 32px;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: skeleton-loading 1.5s infinite;
    border-radius: 4px;
    margin-bottom: 20px;
}

.skeleton-line {
    height: 16px;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: skeleton-loading 1.5s infinite;
    border-radius: 4px;
    margin-bottom: 12px;
}

@keyframes skeleton-loading {
    0% {
        background-position: 200% 0;
    }
    100% {
        background-position: -200% 0;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const loader = document.getElementById('pageLoader');
    if (loader) loader.classList.add('active');
});

window.addEventListener('load', function() {
    const loader = document.getElementById('pageLoader');
    if (loader) {
        setTimeout(() => {
            loader.classList.remove('active');
        }, 300);
    }
});
</script>
-->