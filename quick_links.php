<style>

/* Quick Links Container */
.modern-ql-section {
    background: #ffffff;
    padding: 50px 0;
    width: 100%;
    position: relative;
    overflow: hidden;
 
}

/* Header */
.modern-ql-header {
    text-align: center;
    margin-bottom: 35px;
}

.modern-ql-eyebrow {
    display: block;
    color: #bc2121;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-bottom: 10px;
}

.modern-ql-header h2 {
    border-bottom: none !important;
    text-decoration: none !important;
    box-shadow: none !important;
    color: #bc2121;
    font-size: 26px;
    font-weight: 700;
    letter-spacing: 0.5px;
    margin: 0;
}

.modern-ql-header h2::before,
.modern-ql-header h2::after {
    display: none !important;
    content: none !important;
}

/* Tight Grid Padding */
.modern-ql-grid [class*="col-"] {
    padding-left: 10px;
    padding-right: 10px;
}

/* Quick Link Box */
.modern-ql-box {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #bc2121;
    border: 1px solid #e3e0d8;
    padding: 18px 20px;
    margin-bottom: 20px;
    border-radius: 6px;
    text-decoration: none !important;
    position: relative;
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    min-height: 60px;
}

/* Typography inside Box */
.ql-text {
    color: #ffffff;
    font-size: 14px;
    font-weight: 500;
    letter-spacing: 0.3px;
    transition: color 0.3s ease;
    padding-right: 10px;
}

/* Arrow Indicator */
.ql-arrow {
    color: #ffffff;
    font-size: 18px;
    opacity: 1;
    transform: translateX(0);
    transition: all 0.3s ease;
}

/* Hover Micro-Interactions */
.modern-ql-box:hover {
    background: #ffffff;
    border-color: #1b3a6b;
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(27, 42, 60, 0.12);
}

.modern-ql-box:hover .ql-text {
    color: #1b3a6b;
}

.modern-ql-box:hover .ql-arrow {
    color: #1b3a6b;
    transform: translateX(4px);
}

</style>

<div class="modern-ql-section">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="modern-ql-header">
                    <h2>Quick Links</h2>
                </div>
            </div>
        </div>

        <div class="row modern-ql-grid">
            <div class="col-md-3 col-sm-6 col-xs-12">
                <a href="events_activities.php" class="modern-ql-box">
                    <span class="ql-text">Upcoming Events</span>
                    <span class="ql-arrow">&#10230;</span>
                </a>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <a href="work-plan-activities.php" class="modern-ql-box">
                    <span class="ql-text">Work Plan & Activities</span>
                    <span class="ql-arrow">&#10230;</span>
                </a>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <a href="patents-innovations.php" class="modern-ql-box">
                    <span class="ql-text">Patents & Innovations</span>
                    <span class="ql-arrow">&#10230;</span>
                </a>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <a href="outcomes_impact.php" class="modern-ql-box">
                    <span class="ql-text">Outcomes & Impact</span>
                    <span class="ql-arrow">&#10230;</span>
                </a>
            </div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <a href="collobrations.php" class="modern-ql-box">
                    <span class="ql-text">Collaborations</span>
                    <span class="ql-arrow">&#10230;</span>
                </a>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <a href="gallery.php" class="modern-ql-box">
                    <span class="ql-text">Photo & Video Gallery</span>
                    <span class="ql-arrow">&#10230;</span>
                </a>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <a href="acknowledgment.php" class="modern-ql-box">
                    <span class="ql-text">Project Acknowledgment</span>
                    <span class="ql-arrow">&#10230;</span>
                </a>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <a href="#" class="modern-ql-box">
                    <span class="ql-text">Resource Archive</span>
                    <span class="ql-arrow">&#10230;</span>
                </a>
            </div>
        </div>
    </div>
</div>