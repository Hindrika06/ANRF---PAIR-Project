    <!-- WHATS NEW SCROLLING -->
        <div class="whatsnew-bar">
            <div class="whatsnew-title">What's New</div>
            <div class="whatsnew-scroll">
                <marquee behavior="scroll" direction="left" scrollamount="6">
                    <a href="event-detail.html">
                        📢 Webinar on SMART NANO BIOSENSORS – May 20, 2026
                    </a>
                    &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
                    <a href="events_activities.php">
                        🎓 Osmania University Education Week: May 11–17, 2026
                    </a>
                    &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
                    <a href="gallery.php">
                        📸 New Event Photos Uploaded in Gallery
                    </a>
                    &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
                </marquee>
            </div>
        </div>


        <style>
/* WHATS NEW BAR */
.whatsnew-bar{
    display:flex;
    align-items:center;
    background:#ffffff;
    border-top:3px solid #bc2121;
    border-bottom:1px solid #ddd;
    box-shadow:0 2px 8px rgba(0,0,0,0.05);
    margin-bottom:20px;
}

.whatsnew-title{
    background:#bc2121;
    color:#fff;
    padding:14px 22px;
    font-size:16px;
    font-weight:700;
    white-space:nowrap;
}

.whatsnew-scroll{
    flex:1;
    padding:0 15px;
}

.whatsnew-scroll a{
    color:#2f5fb8;
    font-size:14px;
    font-weight:600;
    text-decoration:none;
}

.whatsnew-scroll a:hover{
    color:#bc2121;
}

/* MOBILE */
@media(max-width:768px){

    .whatsnew-title{
        padding:12px 14px;
        font-size:13px;
    }

    .whatsnew-scroll a{
        font-size:12px;
    }

}
</style>