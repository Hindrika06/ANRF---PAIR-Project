<style>
    /* ======= SECTION WRAPPER ======= */
    .combined-section {
        background: #ffffff;
        padding: 30px 100px;
    }

    .combined-inner {
        display: flex;
        gap: 40px;
        align-items: flex-start;
    }

    /* ======= ABOUT SECTION (original styling) ======= */
    .anrf-intro-text {
        flex: 1;
        min-width: 0;
        text-align: left;
    }

    .anrf-intro-heading-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 40px;
    }

    .anrf-intro-heading-row h4,
    .anrf-intro-heading-row h2.anrf-tagline {
        font-size: 2.5rem;
        font-weight: 500;
        margin: 0;
        line-height: 1.3;
    }

    .anrf-intro-heading-row h4 {
        color: #14213a;
    }

    .anrf-intro-heading-row h2.anrf-tagline {
        color: #bc2121;
    }

    .anrf-intro-image {
        margin-bottom: 45px;
    }

    .anrf-intro-image img {
        width: 100%;
        display: block;
    }

    .anrf-intro-text h3 {
        font-size: 2.2rem;
        color: #1b3a6b;
        font-weight: 700;
        margin-top: 45px;
        margin-bottom: 18px;
        text-align: left;
    }

    .anrf-intro-text p {
        font-size: 21px;
        line-height: 1.9;
        color: #333;
        text-align: left;
    }

    .anrf-intro-text ul {
        list-style: disc;
        padding-left: 28px;
        margin: 0;
        text-align: left;
    }

    .anrf-intro-text ul li {
        font-size: 21px;
        line-height: 2.1;
        color: #333;
    }

    /* ======= CALENDAR + NOTICE + QUICK LINKS SIDEBAR ======= */
    .cal-notice-sidebar {
        flex: 0 0 300px;
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    /* ======= CALENDAR STYLES ======= */
    .custom-calendar-widget {
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 3px 12px rgba(0,0,0,0.06);
    }

    .custom-calendar-header {
        background: #2f5fb8;
        color: #fff;
        text-align: center;
        padding: 12px 10px;
        font-size: 15px;
        font-weight: 600;
    }

    .custom-calendar-body {
        padding: 16px;
    }

    .custom-calendar-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }

    .custom-month-year {
        text-align: center;
    }

    .custom-month-year h3 {
        margin: 0;
        font-size: 17px;
        color: #333;
        font-weight: 600;
        line-height: 1;
    }

    .custom-month-year p {
        margin: 3px 0 0;
        font-size: 12px;
        color: #777;
    }

    .custom-nav-btn {
        width: 30px;
        height: 30px;
        border: 1px solid #ddd;
        border-radius: 50%;
        background: #fff;
        font-size: 13px;
        color: #888;
        cursor: pointer;
        transition: 0.3s;
        padding: 0;
        line-height: 1;
    }

    .custom-nav-btn:hover {
        background: #2f5fb8;
        color: #fff;
        border-color: #2f5fb8;
    }

    .custom-calendar-table {
        width: 100%;
        text-align: center;
        border-collapse: collapse;
    }

    .custom-calendar-table th {
        padding: 6px 0;
        font-size: 11px;
        color: #555;
        font-weight: 600;
    }

    .custom-calendar-table td {
        padding: 8px 0;
        font-size: 13px;
        color: #444;
        cursor: pointer;
        border-radius: 4px;
        transition: background 0.2s;
    }

    .custom-calendar-table td:not(.muted):hover {
        background: #e8eef8;
        border-radius: 50%;
    }

    .custom-calendar-table td.today {
        background: #2f5fb8;
        color: #fff;
        border-radius: 50%;
        font-weight: bold;
    }

    .custom-calendar-table td.muted {
        color: #ccc;
        cursor: default;
    }

    .custom-calendar-table td.has-event {
        position: relative;
    }

    .custom-calendar-table td.has-event::after {
        content: '';
        display: block;
        width: 5px;
        height: 5px;
        background: #bc2121;
        border-radius: 50%;
        margin: 1px auto 0;
    }

    .custom-calendar-table td.today.has-event::after {
        background: #fff;
    }

    /* ======= SELECTED DATE INFO ======= */
    .cal-selected-info {
        padding: 10px 16px 14px;
        border-top: 1px solid #f0f0f0;
        min-height: 48px;
    }

    .cal-selected-info .sel-date-label {
        font-size: 11px;
        color: #999;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 4px;
    }

    .cal-selected-info .sel-event-text {
        font-size: 13px;
        color: #333;
        line-height: 1.5;
    }

    .cal-selected-info .sel-event-text.no-event {
        color: #aaa;
        font-style: italic;
    }

    /* ======= NOTICE BOARD ======= */
    .custom-notice-board {
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 3px 12px rgba(0,0,0,0.06);
    }

    .custom-notice-header {
        background: #2f5fb8;
        color: #fff;
        text-align: center;
        padding: 12px 10px;
        font-size: 15px;
        font-weight: 600;
    }

    .custom-notice-body {
        padding: 14px 16px;
    }

    .notice-item {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        padding: 10px 0;
        border-bottom: 1px solid #f2f2f2;
    }

    .notice-item:last-child {
        border-bottom: none;
    }

    .notice-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #2f5fb8;
        flex-shrink: 0;
        margin-top: 5px;
    }

    .notice-item p {
        font-size: 13px;
        color: #444;
        line-height: 1.55;
        margin: 0;
    }

    .notice-item span {
        display: block;
        font-size: 11px;
        color: #999;
        margin-top: 2px;
    }

    .notice-empty {
        font-size: 13px;
        color: #aaa;
        font-style: italic;
        padding: 6px 0;
    }

    /* ======= QUICK LINKS (CLEAN WHITE CARD STYLE) ======= */
    .sidebar-ql-section {
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 3px 12px rgba(0,0,0,0.06);
    }

    .sidebar-ql-header {
        background: #2f5fb8;
        color: #fff;
        text-align: center;
        padding: 12px 10px;
        font-size: 15px;
        font-weight: 600;
    }

    .sidebar-ql-body {
        padding: 8px 10px 12px;
        display: flex;
        flex-direction: column;
    }

    .sidebar-ql-box {
        display: flex;
        align-items: center;
        gap: 12px;
        background: transparent;
        padding: 11px 8px;
        border-radius: 8px;
        text-decoration: none !important;
        border-bottom: 1px solid #f2f2f2;
        transition: background 0.2s ease, transform 0.2s ease;
    }

    .sidebar-ql-body .sidebar-ql-box:last-child {
        border-bottom: none;
    }

    .sidebar-ql-icon {
        flex-shrink: 0;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: #fdecec;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s ease;
    }

    .sidebar-ql-icon svg {
        width: 17px;
        height: 17px;
        stroke: #bc2121;
        fill: none;
        stroke-width: 1.8;
        stroke-linecap: round;
        stroke-linejoin: round;
        transition: stroke 0.2s ease;
    }

    .sidebar-ql-text {
        flex: 1;
        color: #2a2a2a;
        font-size: 13.5px;
        font-weight: 500;
        letter-spacing: 0.2px;
        transition: color 0.2s ease;
    }

    .sidebar-ql-arrow {
        color: #bc2121;
        font-size: 16px;
        flex-shrink: 0;
        transform: translateX(0);
        transition: all 0.25s ease;
    }

    .sidebar-ql-box:hover {
        background: #f7f9fc;
    }

    .sidebar-ql-box:hover .sidebar-ql-icon {
        background: #bc2121;
    }

    .sidebar-ql-box:hover .sidebar-ql-icon svg {
        stroke: #ffffff;
    }

    .sidebar-ql-box:hover .sidebar-ql-text {
        color: #1b3a6b;
    }

    .sidebar-ql-box:hover .sidebar-ql-arrow {
        color: #1b3a6b;
        transform: translateX(3px);
    }

    /* ======= RESPONSIVE ======= */
    @media (max-width: 900px) {
        .combined-section {
            padding: 10px 30px;
        }

        .combined-inner {
            flex-direction: column;
            gap: 30px;
        }

        .cal-notice-sidebar {
            flex: none;
            width: 100%;
        }

        .anrf-intro-heading-row {
            flex-direction: column;
            align-items: flex-start;
            gap: 5px;
        }

        .anrf-intro-heading-row h4,
        .anrf-intro-heading-row h2.anrf-tagline {
            font-size: 1.8rem;
        }

        .anrf-intro-text h3 {
            font-size: 1.6rem;
        }

        .anrf-intro-text p,
        .anrf-intro-text ul li {
            font-size: 17px;
        }
    }

    @media (max-width: 480px) {
        .custom-calendar-table td,
        .custom-calendar-table th {
            padding: 6px 0;
            font-size: 11px;
        }

        .custom-month-year h3 {
            font-size: 15px;
        }
    }
</style>

<!-- ======= COMBINED SECTION ======= -->
<div class="combined-section">
    <div class="combined-inner">

        <!-- LEFT: About ANRF-PAIR -->
        <div class="anrf-intro-text">
            <div class="anrf-intro-heading-row">
                <h4>Innovations in Health and Medical Technologies Sustainable Health For a Resilient Future</h4>
            </div>

            <div class="anrf-intro-image">
                <img src="assets/img/main1.png" alt="Innovations in Health and Medical Technologies" onerror="this.style.display='none'">
            </div>

            <h3>About ANRF&ndash;PAIR</h3>
            <p>The ANRF&ndash;PAIR initiative is a flagship program aimed at fostering collaborative interdisciplinary research between premier institutions and partner universities. The project promotes sustainable and technology-driven healthcare solutions and strengthens India's research ecosystem.</p>

            <h3>Vision</h3>
            <p>To establish a globally competitive research ecosystem that drives innovation in health technologies.</p>

            <h3>Mission</h3>
            <ul>
                <li>Promote interdisciplinary research</li>
                <li>Foster collaboration</li>
                <li>Enhance innovation culture</li>
                <li>Support technology transfer</li>
            </ul>
        </div>

        <!-- RIGHT: Calendar + Notice Board + Quick Links -->
        <div class="cal-notice-sidebar">

            <!-- Calendar Widget -->
            <aside class="custom-calendar-widget">
                <div class="custom-calendar-header">Event Calendar</div>
                <div class="custom-calendar-body">
                    <div class="custom-calendar-top">
                        <button id="prevMonth" class="custom-nav-btn" aria-label="Previous month">&#10094;</button>
                        <div class="custom-month-year">
                            <h3 id="month"></h3>
                            <p id="year"></p>
                        </div>
                        <button id="nextMonth" class="custom-nav-btn" aria-label="Next month">&#10095;</button>
                    </div>
                    <table class="custom-calendar-table">
                        <thead>
                            <tr>
                                <th>Sun</th>
                                <th>Mon</th>
                                <th>Tue</th>
                                <th>Wed</th>
                                <th>Thu</th>
                                <th>Fri</th>
                                <th>Sat</th>
                            </tr>
                        </thead>
                        <tbody id="calendar-body"></tbody>
                    </table>
                </div>
                <div class="cal-selected-info" id="cal-selected-info">
                    <div class="sel-date-label">Selected Date</div>
                    <div class="sel-event-text no-event" id="sel-event-text">Click a date to see events</div>
                </div>
            </aside>

            <!-- Notice Board -->
            <aside class="custom-notice-board">
                <div class="custom-notice-header">Notice Board</div>
                <div class="custom-notice-body" id="notice-board-body">
                    <!-- Notices injected by JS -->
                </div>
            </aside>

            <!-- Quick Links (clean white card, icon style) -->
            <aside class="sidebar-ql-section">
                <div class="sidebar-ql-header">Quick Links</div>
                <div class="sidebar-ql-body">
                    <a href="events_activities.php" class="sidebar-ql-box">
                        <span class="sidebar-ql-icon">
                            <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        </span>
                        <span class="sidebar-ql-text">Upcoming Events</span>
                        <span class="sidebar-ql-arrow">&#10230;</span>
                    </a>
                    <a href="work-plan-activities.php" class="sidebar-ql-box">
                        <span class="sidebar-ql-icon">
                            <svg viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                        </span>
                        <span class="sidebar-ql-text">Work Plan & Activities</span>
                        <span class="sidebar-ql-arrow">&#10230;</span>
                    </a>
                    <a href="patents-innovations.php" class="sidebar-ql-box">
                        <span class="sidebar-ql-icon">
                            <svg viewBox="0 0 24 24"><path d="M9 18h6"/><path d="M10 22h4"/><path d="M12 2a7 7 0 0 0-4 12.7V17h8v-2.3A7 7 0 0 0 12 2z"/></svg>
                        </span>
                        <span class="sidebar-ql-text">Patents & Innovations</span>
                        <span class="sidebar-ql-arrow">&#10230;</span>
                    </a>
                    <a href="outcomes_impact.php" class="sidebar-ql-box">
                        <span class="sidebar-ql-icon">
                            <svg viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                        </span>
                        <span class="sidebar-ql-text">Outcomes & Impact</span>
                        <span class="sidebar-ql-arrow">&#10230;</span>
                    </a>
                    <a href="collobrations.php" class="sidebar-ql-box">
                        <span class="sidebar-ql-icon">
                            <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </span>
                        <span class="sidebar-ql-text">Collaborations</span>
                        <span class="sidebar-ql-arrow">&#10230;</span>
                    </a>
                    <a href="gallery.php" class="sidebar-ql-box">
                        <span class="sidebar-ql-icon">
                            <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                        </span>
                        <span class="sidebar-ql-text">Photo & Video Gallery</span>
                        <span class="sidebar-ql-arrow">&#10230;</span>
                    </a>
                    <a href="acknowledgment.php" class="sidebar-ql-box">
                        <span class="sidebar-ql-icon">
                            <svg viewBox="0 0 24 24"><path d="M12 15a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"/><path d="M8.21 13.89L7 23l5-3 5 3-1.21-9.12"/></svg>
                        </span>
                        <span class="sidebar-ql-text">Project Acknowledgment</span>
                        <span class="sidebar-ql-arrow">&#10230;</span>
                    </a>
                    <a href="#" class="sidebar-ql-box">
                        <span class="sidebar-ql-icon">
                            <svg viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                        </span>
                        <span class="sidebar-ql-text">Resource Archive</span>
                        <span class="sidebar-ql-arrow">&#10230;</span>
                    </a>
                </div>
            </aside>

        </div>

    </div>
</div>

