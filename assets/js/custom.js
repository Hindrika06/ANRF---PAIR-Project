var _days = 'Days';
var _hours = 'Hours';
var _minutes = 'Minutes';
var _seconds = 'Seconds';
var _messageAfterCount = 'The course has Started!';

var $ = jQuery.noConflict();
$(document).ready(function($) {
    "use strict";

    if (location.hash) {
        window.scrollTo(0, 0);
        setTimeout(function() {
            window.scrollTo(0, 0);
        }, 1);
    }

//  Homepage Slider (Flex Slider)

    if ($('.flexslider').length > 0) {
        $('.flexslider').flexslider({
            controlNav: false,
            prevText: "",
            nextText: ""
        });
    }

//  Open tab from another page

    $('a[data-toggle="tab"]').on('show.bs.tab', function(e) {});

    $('#tabs a[href=' + location.hash +']').tab('show');

    $('.secondary-navigation li a').on('click',function (e) {
        $('#tabs a[href=' + this.hash +']').tab('show');
    });

//  Table Sorter
    if ($('.tablesorter').length > 0) {
        $(".course-list-table").tablesorter();
    }

//  Rating

    if ($('.rating-individual').length > 0) {
        $('.rating-individual').raty({
            path: 'assets/img',
            readOnly: true,
            score: function() {
                return $(this).attr('data-score');
            }
        });
    }

    if ($('.rating-user').length > 0) {
        $('.rating-user .inner').raty({
            path: 'assets/img',
            starOff : 'big-star-off.png',
            starOn  : 'big-star-on.png',
            width: 180,
            target : '#hint',
            targetType : 'number',
            targetFormat : 'Rating: {score}',
            click: function(score, evt) {
                alert("Your Rating: " + score + "\nThank You!");
            }
        });
    }

//  Checkbox styling

    if ($('.checkbox').length > 0) {
        $('input').iCheck();
    }

// Disable input on count down

    $('.knob').prop("disabled", true);


//  Count Down - Landing Page

    if ($('.count-down').length > 0) {
        $(".count-down").ccountdown(2014,12,24,'18:00');
    }

//  Selectize

    $('select').selectize();

//  Center Slide Vertically

    $('.flexslider').each(function () {
        var slideHeight = $(this).height();
        var contentHeight = $('.flexslider .slides li .slide-wrapper').height();
        var padTop = (slideHeight / 2) - (contentHeight / 2);
        $('.flexslider .slides li .slide-wrapper').css('padding-top', padTop);
    });

//  Slider height on small screens

    if (document.documentElement.clientWidth < 991) {
        $('#landing-page-head-image').css('height', $(window).height());
        $('.flexslider').css('height', $(window).height());
    }

//  Homepage Carousel

    $(".image-carousel").owlCarousel({
        items: 1,
        autoPlay: true,
        stopOnHover: true,
        navigation: true,
        navigationText : false,
        responsiveBaseWidth: ".image-carousel-slide"
        //responsiveBaseWidth: ".author"
    });

//  Smooth Scroll

    $('.navigation-wrapper .nav a[href^="#"], a[href^="#"].roll').on('click',function (e) {
        e.preventDefault();
        var target = this.hash,
            $target = $(target);
        $('html, body').stop().animate({
            'scrollTop': $target.offset().top
        }, 2000, 'swing', function () {
            window.location.hash = target;
        });
    });

//  Fixed Navigation After Scroll

//    if (document.documentElement.clientWidth > 768) {
//        $(window).scroll(function () {
//            if ($(window).scrollTop() > 50) {
//                $('.page-landing-page .primary-navigation-wrapper').addClass('navigation-fixed');
//            } else {
//                $('.page-landing-page .primary-navigation-wrapper').removeClass('navigation-fixed');
//            }
//        });
//    }


//  author Carousel (Owl Carousel)

    $(".author-carousel").owlCarousel({
        items: 1,
        autoPlay: false,
        stopOnHover: true,
        responsiveBaseWidth: ".author"
    });

//  Equal Rows

    if(document.documentElement.clientWidth > 991) {
        $('.row').equalHeights();
    }

    $( document.body ).on( 'click', '.dropdown-menu li', function( event ) {
        var $target = $( event.currentTarget );
        $target.closest( '.btn-group' )
            .find( '[data-bind="label"]' ).text( $target.text() )
            .end()
            .children( '.dropdown-toggle' ).dropdown( 'toggle' );
        return false;
    });

//  Slider Subscription Form

    $("#slider-submit").bind("click", function(event){
        $("#slider-form").validate({
            submitHandler: function() {
                $.post("slider-form.php", $("#slider-form").serialize(),  function(response) {
                    $('#form-status').html(response);
                    $('#submit').attr('disabled','true');
                });
                return false;
            }
        });
    });

//  Contact Form with validation

    $("#submit").bind("click", function(event){
        $("#contactform").validate({
            submitHandler: function() {
                $.post("contact.php", $("#contactform").serialize(),  function(response) {
                    $('#form-status').html(response);
                    $('#submit').attr('disabled','true');
                });
                return false;
            }
        });
    });

//  Landing Page Form

    $("#landing-page-submit").bind("click", function(event){
        $("#form-landing-page").validate({
            submitHandler: function() {
                $.post("landing-page-form.php", $("#form-landing-page").serialize(),  function(response) {
                    $('#form-status').html(response);
                    $('#submit').attr('disabled','true');
                });
                return false;
            }
        });
    });

//  Vanilla Box

    if ($('.image-popup').length > 0) {
        $('a.image-popup').vanillabox({
            animation: 'default',
            type: 'image',
            closeButton: true,
            repositionOnScroll: true
        });
    }

//  Calendar

    if ($('.calendar').length > 0) {
        $('.calendar').fullCalendar({
            firstDay: 1,
            weekMode: 'variable',
            contentHeight: 700,
            header: {
                right: 'month,basicWeek,basicDay prev,next'
            },

            events: "events.php"

        });
    }

    // Interactive event calendar for about-1.php
    function initializeEventBoard() {
        const noticeBody = document.getElementById('notice-board-body');
        const monthEl = document.getElementById('month');
        const yearEl = document.getElementById('year');
        const calBody = document.getElementById('calendar-body');
        const selectedLabel = document.querySelector('.sel-date-label');
        const selectedText = document.getElementById('sel-event-text');

        if (!noticeBody || !monthEl || !yearEl || !calBody || !selectedText || !selectedLabel) {
            return;
        }

        const calendarEvents = {
            '2026-07-09': [
                {
                    title: 'University of Hyderabad Research Symposium',
                    time: '10:00 AM - 1:00 PM',
                    venue: 'Conference Hall A',
                    coordinator: "Dr. Ramesh Kumar"
                }
            ],
            '2026-07-14': [
                {
                    title: 'ANRF-PAIR Innovation Showcase',
                    time: '11:00 AM - 2:00 PM',
                    venue: 'Innovation Hub',
                    coordinator: "Dr. Priya Sharma"
                },
                {
                    title: 'Research Seminar on Sustainable Health',
                    time: '3:00 PM - 4:30 PM',
                    venue: 'Seminar Room 3',
                    coordinator: "Dr. Anil Patel"
                }
            ],
            '2026-07-21': [
                {
                    title: 'Strengthening Health Systems Workshop',
                    time: '10:00 AM - 12:30 PM',
                    venue: 'Seminar Room 2',
                    coordinator: "Dr. Meera Reddy"
                }
            ]
        };

        const monthNames = [
            'January','February','March','April','May','June',
            'July','August','September','October','November','December'
        ];

        let currentDate = new Date();

        function formatDateLabel(dateKey) {
            const [year, month, day] = dateKey.split('-');
            return `${parseInt(day, 10)} ${monthNames[parseInt(month, 10) - 1]} ${year}`;
        }

        function renderNoEvents(dateKey) {
            noticeBody.innerHTML = `
                <div class="notice-summary">
                    <div class="notice-summary-date">${formatDateLabel(dateKey)}</div>
                    <div class="notice-empty">No events scheduled for this date.</div>
                </div>
            `;
            selectedText.textContent = 'No events scheduled for this date.';
            selectedText.classList.add('no-event');
        }

        function renderEvents(dateKey, events) {

    const eventList = events.map(function(event) {

        return `
            <li class="notice-summary-item">

                <div class="notice-event-title">
                    ${event.title}
                </div>

                <div class="notice-event-meta">
                    <span class="notice-event-time">
                        ⏰ ${event.time}
                    </span>
                </div>

                <div class="notice-event-meta">
                    <span class="notice-event-venue">
                        📍 ${event.venue}
                    </span>
                </div>

                <div class="notice-event-meta">
                    <span class="notice-event-coordinator">
                        👤 Coordinator: ${event.coordinator}
                    </span>
                </div>

            </li>
        `;

    }).join('');

    noticeBody.innerHTML = `
        <div class="notice-summary">
            <div class="notice-summary-date">
                ${formatDateLabel(dateKey)}
            </div>

            <ul class="notice-summary-list">
                ${eventList}
            </ul>
        </div>
    `;

    selectedText.textContent =
        `${events.length} event${events.length > 1 ? 's' : ''} on ${formatDateLabel(dateKey)}`;

    selectedText.classList.remove('no-event');
}

        function buildCalendar() {
            const monthIndex = currentDate.getMonth();
            const year = currentDate.getFullYear();
            const firstDay = new Date(year, monthIndex, 1).getDay();
            const lastDate = new Date(year, monthIndex + 1, 0).getDate();
            const prevLastDate = new Date(year, monthIndex, 0).getDate();
            const today = new Date();

            monthEl.textContent = monthNames[monthIndex];
            yearEl.textContent = year;

            let html = '';
            let dayNumber = 1;
            let nextMonthDay = 1;

            for (let week = 0; week < 6; week++) {
                html += '<tr>';
                for (let weekday = 0; weekday < 7; weekday++) {
                    if (week === 0 && weekday < firstDay) {
                        html += `<td class="muted">${prevLastDate - firstDay + weekday + 1}</td>`;
                    } else if (dayNumber > lastDate) {
                        html += `<td class="muted">${nextMonthDay++}</td>`;
                    } else {
                        const dateKey = `${year}-${String(monthIndex + 1).padStart(2, '0')}-${String(dayNumber).padStart(2, '0')}`;
                        const isToday = dayNumber === today.getDate() && monthIndex === today.getMonth() && year === today.getFullYear();
                        const hasEventClass = calendarEvents[dateKey] ? 'has-event' : '';

                        html += `<td class="${isToday ? 'today' : ''} ${hasEventClass}" data-date="${dateKey}">${dayNumber}</td>`;
                        dayNumber++;
                    }
                }
                html += '</tr>';
                if (dayNumber > lastDate) {
                    break;
                }
            }

            calBody.innerHTML = html;
            bindCalendarEvents();
        }

        function bindCalendarEvents() {
            const allDates = calBody.querySelectorAll('td[data-date]');
            allDates.forEach(function (cell) {
                cell.addEventListener('click', function () {
                    const dateKey = this.getAttribute('data-date');
                    selectedLabel.textContent = formatDateLabel(dateKey);
                    if (calendarEvents[dateKey] && calendarEvents[dateKey].length > 0) {
                        renderEvents(dateKey, calendarEvents[dateKey]);
                    } else {
                        renderNoEvents(dateKey);
                    }
                });
            });
        }

        document.getElementById('prevMonth').addEventListener('click', function () {
            currentDate.setMonth(currentDate.getMonth() - 1);
            buildCalendar();
            selectedLabel.textContent = 'Selected Date';
            selectedText.textContent = 'Click a date to see events';
            selectedText.classList.add('no-event');
            renderNoEvents(formatDateLabel(`${currentDate.getFullYear()}-${String(currentDate.getMonth() + 1).padStart(2, '0')}-${String(currentDate.getDate()).padStart(2, '0')}`));
        });

        document.getElementById('nextMonth').addEventListener('click', function () {
            currentDate.setMonth(currentDate.getMonth() + 1);
            buildCalendar();
            selectedLabel.textContent = 'Selected Date';
            selectedText.textContent = 'Click a date to see events';
            selectedText.classList.add('no-event');
            renderNoEvents(formatDateLabel(`${currentDate.getFullYear()}-${String(currentDate.getMonth() + 1).padStart(2, '0')}-${String(currentDate.getDate()).padStart(2, '0')}`));
        });

        buildCalendar();
        renderNoEvents(formatDateLabel(`${currentDate.getFullYear()}-${String(currentDate.getMonth() + 1).padStart(2, '0')}-${String(currentDate.getDate()).padStart(2, '0')}`));
    }

    initializeEventBoard();

//  Event title shorting

    $('.fc-view-month .fc-event-title').each(function(){
        $(this).text($(this).text().substring(0,25));
    });

    // Center Logo (ANRF-PAIR) Animation Setup
    (function() {
        var $logo = $('.logo-pair-img').first();
        if ($logo.length === 0) return;

        var logoSrc = $logo.attr('src');

        function runAnimation() {
            // Read rendered dimensions of the original logo
            var w = $logo[0].offsetWidth;
            var h = $logo[0].offsetHeight;

            // If dimensions are zero (image not yet rendered), skip gracefully
            if (!w || !h) return;

            // Build the wrapper with explicit pixel dimensions so absolute layers work
            var $wrapper = $('<div class="anrf-logo-animation-wrapper"></div>').css({
                width:    w + 'px',
                height:   h + 'px',
                overflow: 'visible'   // allow clip-path to work without being clipped by parent
            });

            // Clone layers — each is a full copy of the logo image, clipped via CSS
            var $staticLogo = $('<img>').attr({ src: logoSrc, alt: '' })
                .addClass('logo-pair-img anrf-logo-layer anrf-static-fadein')
                .css({ width: w + 'px', height: h + 'px', maxHeight: 'none' });

            var $arcs = $('<img>').attr({ src: logoSrc, alt: '' })
                .addClass('logo-pair-img anrf-logo-layer anrf-layer-arcs anrf-animate-arcs')
                .css({ width: w + 'px', height: h + 'px', maxHeight: 'none' });

            var $dot = $('<img>').attr({ src: logoSrc, alt: '' })
                .addClass('logo-pair-img anrf-logo-layer anrf-layer-dot anrf-animate-dot')
                .css({ width: w + 'px', height: h + 'px', maxHeight: 'none' });

            var $text = $('<img>').attr({ src: logoSrc, alt: '' })
                .addClass('logo-pair-img anrf-logo-layer anrf-layer-text anrf-animate-text')
                .css({ width: w + 'px', height: h + 'px', maxHeight: 'none' });

            // Wrap and inject
            $logo.wrap($wrapper);
            var $parent = $logo.parent(); // now the wrapper

            // Hide the real logo during animation
            $logo.hide();

            $parent.append($staticLogo);
            $parent.append($arcs);
            $parent.append($dot);
            $parent.append($text);

            // After animation (2.8 s), clean up and restore
            setTimeout(function() {
                $logo.show();
                $staticLogo.remove();
                $arcs.remove();
                $dot.remove();
                $text.remove();
                if ($logo.parent().hasClass('anrf-logo-animation-wrapper')) {
                    $logo.unwrap();
                }
            }, 2850);
        }

        // Run after the logo image has fully loaded (needed for offsetWidth/Height)
        if ($logo[0].complete && $logo[0].naturalWidth > 0) {
            runAnimation();
        } else {
            $logo.on('load', runAnimation);
        }
    })();

});


// Remove button function for "join to course" button after count down is over

function disableJoin() {
    // Find "join to course" button
    var buttonToBeRemoved = document.getElementById("btn-course-join");
    // Find "join to course" button on bottom of course detail
    var buttonToBeRemovedBottom = document.getElementById("btn-course-join-bottom");
    // Remove button
    buttonToBeRemoved.remove();
    // Remove button on the bottom
    buttonToBeRemovedBottom.remove();
    // Give the ".course-count-down" element new class to hide date
    document.getElementById("course-count-down").className += " disable-join";
    document.getElementById("course-start").className += " disable-join";
}

//  Count Down - Course Detail

if (typeof _date != 'undefined') { // run function only if _date is defined
    var Countdown = new Countdown({
        dateEnd: new Date(_date),
        msgAfter: _messageAfterCount,
        onEnd: function() {
            disableJoin(); // Run this function after count down is over
        }
    });
}



                    function updateCountdown() {
                        const eventDate = new Date('2026-05-20T10:00:00+05:30').getTime();
                        
                        function tick() {
                            const daysEl = document.getElementById('days');
                            if (!daysEl) return;
                            const now = new Date().getTime();
                            const distance = eventDate - now;
                            
                            if (distance < 0) {
                                document.getElementById('days').textContent = '00';
                                document.getElementById('hours').textContent = '00';
                                document.getElementById('minutes').textContent = '00';
                                document.getElementById('seconds').textContent = '00';
                                return;
                            }
                            
                            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                            
                            document.getElementById('days').textContent = String(days).padStart(2, '0');
                            document.getElementById('hours').textContent = String(hours).padStart(2, '0');
                            document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
                            document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');
                        }
                        
                        tick();
                        setInterval(tick, 1000);
                    }

                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', updateCountdown);
                    } else {
                        updateCountdown();
                    }


