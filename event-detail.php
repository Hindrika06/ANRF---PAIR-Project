<?php
require_once 'events_helper.php';

$eventId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$event = null;

if ($eventId > 0) {
    $event = getEventById($eventId);
}

if (!$event) {
    $event = getFeaturedWorkshop();
}

$pageTitle = ($event ? $event['title'] : 'Event Details') . ' | ANRF–PAIR Project';
include 'header.php';
?>

<!-- Breadcrumb -->
<div class="container">
    <ol class="breadcrumb" style="font-size: 14px; margin-bottom: 0; background: transparent; padding-left: 0;">
        <li><a href="index.php">Home</a></li>
        <li><a href="events_activities.php">Events</a></li>
        <li class="active"><?= htmlspecialchars($event['event_type'] ?? 'Event') ?> Details</li>
    </ol>
</div>

<div id="page-content" style="padding-bottom: 50px;">
    <div class="container">
        <?php if ($event): ?>
            <?php
                $startDateFormatted = date("d F, Y", strtotime($event['event_date']));
                $endDateFormatted = !empty($event['end_date']) && $event['end_date'] !== $event['event_date']
                    ? ' – ' . date("d F, Y", strtotime($event['end_date']))
                    : '';
                $startTime = date("g:i A", strtotime($event['start_time']));
                $endTime = date("g:i A", strtotime($event['end_time']));
            ?>

            <!-- Event Title & Header Block -->
            <div style="background: #ffffff; padding: 25px 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 30px; border-left: 5px solid #bc2121;">
                <span style="display: inline-block; background: #bc2121; color: #fff; font-size: 12px; font-weight: 700; text-transform: uppercase; padding: 4px 10px; border-radius: 4px; margin-bottom: 10px;">
                    <?= htmlspecialchars($event['event_type']) ?>
                </span>
                <h1 style="color: #1b3a6b; font-size: 26px; font-weight: 800; margin: 0 0 15px; line-height: 1.3;">
                    <?= htmlspecialchars($event['title']) ?>
                </h1>
                
                <div style="display: flex; flex-wrap: wrap; gap: 20px; font-size: 14px; color: #475569; font-weight: 500;">
                    <div>📅 <strong>Date:</strong> <?= $startDateFormatted . $endDateFormatted ?></div>
                    <div>⏰ <strong>Time:</strong> <?= $startTime ?> - <?= $endTime ?></div>
                    <div>📍 <strong>Venue:</strong> <?= htmlspecialchars($event['venue']) ?></div>
                    <div>👤 <strong>Coordinator/Convener:</strong> <?= htmlspecialchars($event['coordinator'] ?: ($event['convener'] ?: 'ANRF-PAIR Team')) ?></div>
                </div>
            </div>

            <!-- Banner Image & Main Details -->
            <div class="row">
                <div class="col-md-7">
                    <?php if (!empty($event['image']) && file_exists($event['image'])): ?>
                        <div style="margin-bottom: 25px; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.08);">
                            <img src="<?= htmlspecialchars($event['image']) ?>" alt="<?= htmlspecialchars($event['title']) ?>" style="width: 100%; height: auto; display: block;">
                        </div>
                    <?php endif; ?>

                    <!-- Description / Overview -->
                    <div style="background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.04); margin-bottom: 25px;">
                        <h3 style="color: #1b3a6b; font-size: 18px; font-weight: 700; margin-top: 0; margin-bottom: 12px; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px;">
                            Overview & Details
                        </h3>
                        <div style="font-size: 15px; line-height: 1.8; color: #334155; white-space: pre-line;">
                            <?= htmlspecialchars($event['description']) ?>
                        </div>
                    </div>

                    <!-- Training Schedule -->
                    <?php if (!empty($event['training_schedule'])): ?>
                        <div style="background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.04); margin-bottom: 25px;">
                            <h3 style="color: #1b3a6b; font-size: 18px; font-weight: 700; margin-top: 0; margin-bottom: 12px; border-bottom: 2px solid #f1f5f9; padding-bottom: 8px;">
                                📋 Training Schedule
                            </h3>
                            <div style="font-size: 14.5px; line-height: 1.8; color: #334155; white-space: pre-line;">
                                <?= htmlspecialchars($event['training_schedule']) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Right Sidebar Details -->
                <div class="col-md-5">
                    <!-- Resource Person & Leadership -->
                    <div style="background: #fff; padding: 22px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.04); margin-bottom: 25px;">
                        <h3 style="color: #bc2121; font-size: 17px; font-weight: 700; margin-top: 0; margin-bottom: 15px; border-bottom: 2px solid #fee2e2; padding-bottom: 8px;">
                            Organization & Leadership
                        </h3>
                        
                        <?php if (!empty($event['organizer'])): ?>
                            <p style="font-size: 14px; color: #1e293b; margin-bottom: 12px;">
                                <strong>Organized By:</strong><br><?= htmlspecialchars($event['organizer']) ?>
                            </p>
                        <?php endif; ?>

                        <?php if (!empty($event['resource_person'])): ?>
                            <div style="background: #f8fafc; padding: 12px; border-radius: 6px; margin-bottom: 12px; border-left: 3px solid #0b4c8c;">
                                <strong style="color: #0b4c8c; font-size: 13.5px;">Resource Person:</strong>
                                <p style="font-size: 13.5px; color: #334155; margin: 4px 0 0;"><?= htmlspecialchars($event['resource_person']) ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($event['chief_patron'])): ?>
                            <p style="font-size: 13.5px; color: #334155; margin-bottom: 8px;"><strong>Chief Patron:</strong> <?= htmlspecialchars($event['chief_patron']) ?></p>
                        <?php endif; ?>

                        <?php if (!empty($event['patrons'])): ?>
                            <p style="font-size: 13.5px; color: #334155; margin-bottom: 8px;"><strong>Patrons:</strong> <?= htmlspecialchars($event['patrons']) ?></p>
                        <?php endif; ?>

                        <?php if (!empty($event['convener'])): ?>
                            <p style="font-size: 13.5px; color: #334155; margin-bottom: 8px;"><strong>Convener:</strong> <?= htmlspecialchars($event['convener']) ?></p>
                        <?php endif; ?>

                        <?php if (!empty($event['organising_committee'])): ?>
                            <p style="font-size: 13.5px; color: #334155; margin-bottom: 0;"><strong>Organising Committee:</strong> <?= htmlspecialchars($event['organising_committee']) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Registration & QR Code -->
                    <?php if (!empty($event['registration_guidelines']) || !empty($event['qr_code_image'])): ?>
                        <div style="background: #fff; padding: 22px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.04); text-align: center;">
                            <h3 style="color: #bc2121; font-size: 17px; font-weight: 700; margin-top: 0; margin-bottom: 12px; border-bottom: 2px solid #fee2e2; padding-bottom: 8px; text-align: left;">
                                📝 Registration
                            </h3>

                            <?php if (!empty($event['registration_guidelines'])): ?>
                                <div style="font-size: 13.5px; color: #475569; text-align: left; margin-bottom: 15px; white-space: pre-line; line-height: 1.6;">
                                    <?= htmlspecialchars($event['registration_guidelines']) ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($event['qr_code_image']) && file_exists($event['qr_code_image'])): ?>
                                <div style="display: inline-block; padding: 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                                    <img src="<?= htmlspecialchars($event['qr_code_image']) ?>" alt="Registration QR Code" style="max-width: 160px; height: auto; display: block; margin: 0 auto;">
                                    <span style="font-size: 12px; font-weight: 700; color: #bc2121; display: block; margin-top: 6px;">Scan to Register</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning" style="margin-top: 30px; text-align: center;">
                No event details found. <a href="events_activities.php">View all upcoming events</a>.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
