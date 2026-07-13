    <style>
        /* --- Clean Breadcrumbs --- */
        .breadcrumb {
            padding: 24px 0 12px 0;
            list-style: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #64748b;
        }
        .breadcrumb a { 
            color: #475569; 
            text-decoration: none; 
            transition: color 0.2s ease;
        }
        .breadcrumb a:hover { color: #bc2121; }
        .breadcrumb li:not(:last-child)::after { 
            content: "•"; 
            margin-left: 8px; 
            color: #cbd5e1; 
            font-size: 12px;
        }
        .breadcrumb li.active { color: #0f172a; font-weight: 500; }

        /* --- Centered and Unbolded Main Gallery Heading --- */
        .main-gallery-title {
            text-align: center;
            font-size: 36px;
            font-weight: 400; /* Unbolded */
            color: #0f172a;
            margin: 40px 0 50px 0;
            letter-spacing: -0.5px;
        }
        .main-gallery-title::after {
            content: '';
            display: block;
            width: 40px;
            height: 3px;
            background: #bc2121;
            margin: 16px auto 0 auto;
        }

        /* --- Event Section Separation Gap --- */
        .event-block {
            margin-bottom: 60px; /* Refined vertical grid spacing */
        }
        .event-block:last-child {
            margin-bottom: 40px; /* Prevents an excessive gap above the footer */
        }

        /* --- Tighter, Stylized Event Header --- */
        .event-header-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            margin-bottom: 14px; /* Reduced gap to pull close to photos */
            padding-bottom: 6px;
            text-align: center;
            position: relative;
        }

        .event-title-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            width: 100%;
        }

        /* Centered and Clean Event Title */
        .event-title {
            font-size: 20px;
            font-weight: 500; 
            color: #1e293b;
            margin: 0;
            letter-spacing: -0.3px;
        }

        /* Accent indicator line bridging heading and images */
        .event-title::after {
            content: '';
            display: block;
            width: 24px;
            height: 2px;
            background: #e2e8f0;
            margin: 6px auto 0 auto;
            transition: background 0.3s ease;
        }
        .event-block:hover .event-title::after {
            background: #bc2121; /* Interactive dynamic color flip */
        }

        /* Institutional micro-copy under event title */
        .event-meta {
            font-size: 13px;
            color: #64748b;
        }

        /* Media photo quantity badge */
        .image-count-badge {
            background-color: #f1f5f9;
            color: #475569;
            font-size: 12px;
            font-weight: 500;
            padding: 6px 12px;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            display: inline-block;
        }

        /* --- Custom Horizontal Slider Controls --- */
        .gallery-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .gallery-list-horizontal {
            display: flex;
            overflow-x: auto;
            scroll-behavior: smooth;
            list-style: none;
            padding: 4px 0 16px 0;
            margin: 0;
            gap: 24px;
            width: 100%;
            scrollbar-width: none; /* Hide standard scrollbar */
        }
        .gallery-list-horizontal::-webkit-scrollbar { display: none; }

        /* --- Image Card Presentation --- */
        .gallery-list-horizontal li {
            flex: 0 0 calc(25% - 18px);
            min-width: 260px;
        }

        .image-card {
            display: block;
            position: relative;
            overflow: hidden;
            background: #f8fafc;
            aspect-ratio: 4 / 3;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
            transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.4s ease;
        }

        .image-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* Hover interactions */
        .image-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 25px -5px rgba(15, 23, 42, 0.1), 0 10px 10px -5px rgba(15, 23, 42, 0.04);
        }
        .image-card:hover img {
            transform: scale(1.04);
        }

        /* Smooth premium overlay tint with interaction icon */
        .image-card::after {
            content: 'View Photo';
            font-size: 13px;
            font-weight: 500;
            color: #ffffff;
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(15, 23, 42, 0.6), rgba(15, 23, 42, 0.2));
            display: flex;
            align-items: flex-end;
            padding: 16px;
            box-sizing: border-box;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .image-card:hover::after { opacity: 1; }

        /* --- Sleek Integrated Navigation Buttons --- */
        .nav-btn {
            position: absolute;
            background-color: #bc2121; 
            color: #ffffff;            
            border: 1px solid #bc2121; 
            width: 44px;
            height: 44px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            z-index: 5;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 10px rgba(188, 33, 33, 0.25); 
        }

        .nav-btn:hover {
            background-color: #9e1b1b; 
            border-color: #9e1b1b;
            transform: scale(1.08);     
            box-shadow: 0 6px 14px rgba(188, 33, 33, 0.35);
        }

        /* Positioning arrows to neatly straddle the outer borders */
        .btn-left { left: -22px; }
        .btn-right { right: -22px; }

        /* --- Responsive Viewports --- */
        @media (max-width: 1024px) {
            .gallery-list-horizontal li { flex: 0 0 calc(33.333% - 16px); }
            .nav-btn { display: none; } 
            .gallery-list-horizontal { padding-bottom: 8px; }
        }
        @media (max-width: 768px) {
            .event-block { padding: 0; margin-bottom: 40px; }
            .gallery-list-horizontal li { flex: 0 0 calc(50% - 12px); gap: 16px; }
            .event-header-box { margin-bottom: 12px; }
        }
        @media (max-width: 480px) {
            .gallery-list-horizontal li { flex: 0 0 85%; }
            .main-gallery-title { font-size: 28px; margin: 30px 0 35px 0; }
            .event-title { font-size: 18px; }
            .image-count-badge { display: none; } 
        }
    </style>
<?php include 'header.php'; ?>

<?php include 'gallery-1.php'; ?>


<script>
function scrollGallery(btn, direction) {
    const list = btn.parentElement.querySelector('.gallery-list-horizontal');
    const scrollAmount = list.clientWidth * 0.8; 
    
    list.scrollBy({
        left: direction * scrollAmount,
        behavior: 'smooth'
    });
}
</script>

<?php include 'footer.php'; ?>
