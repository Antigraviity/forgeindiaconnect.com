
(function ($) {
    "use strict";

    // Get Device width
    var device_width = window.innerWidth;

    /*======================================
        Preloader activation
    ========================================*/
    $(window).on("load", function (event) {
        
        // Preloader
        $(document).ready(function () {
            $('#container').addClass('loaded');
            if ($('#container').hasClass('loaded')) {
            $('#preloader').delay(1000).queue(function () {
                $(this).remove();
            });
            }
        });
        
        // Text Animation
        setTimeout(() => {
        var hasAnim = $(".anim-text");
            hasAnim.each(function () {
                var $this = $(this);
                var splitto = new SplitType($this, {
                types: "lines, chars",
                className: "char",
                });
                var chars = $this.find(".char");
                gsap.fromTo(
                chars,
                { y: "100%" },
                {
                    y: "0%",
                    duration: 0.9,
                    stagger: 0.03,
                    ease: "power2.out",
                }
                );
            });
        }, 1000);
    });

    $(".preloader-close").on("click", function () {
        $("#preloader").delay(0).fadeOut(500);
    });

    $(document).ready(function () {
        // Dynamic Year
        var currentYear = new Date().getFullYear();
        $('#current-year').text(currentYear);

        if(navigator.userAgent.toLowerCase().indexOf('firefox') > -1){
            $('body').addClass('firefox');
        }
        
        var header = $(".header"),
            stickyHeader = $(".primary-header");

        function menuSticky(w) {
            if (w.matches) {
                
                $(window).on("scroll", function () {
                    var scroll = $(window).scrollTop();
                    if (scroll >= 110) {
                        stickyHeader.addClass("fixed");
                    } else {
                        stickyHeader.removeClass("fixed");
                    }
                });
                if ($(".header").length > 0) {    
                    var  headerHeight = document.querySelector(".header"),
                        setHeaderHeight = headerHeight.offsetHeight;	
                    header.each(function () {
                        $(this).css({
                            'height' : setHeaderHeight + 'px'
                        });
                    });
                }
            }
        }

        var minWidth = window.matchMedia("(min-width: 992px)");
        if (header.hasClass("sticky-active")) {
            menuSticky(minWidth);
        }

        //Mobile Menu Js
        $(".mobile-menu-items").meanmenu({
            meanMenuContainer: ".side-menu-wrap",
            meanScreenWidth: "992",
            meanMenuCloseSize: "30px",
            meanRemoveAttrs: true,
            meanExpand: ['<i class="fa-solid fa-caret-down"></i>'],
        });

        // Mobile Sidemenu
        $(".mobile-side-menu-toggle").on("click", function () {
            $(".mobile-side-menu, .mobile-side-menu-overlay").toggleClass("is-open");
        });

        $(".mobile-side-menu-close, .mobile-side-menu-overlay").on("click", function () {
            $(".mobile-side-menu, .mobile-side-menu-overlay").removeClass("is-open");
        });

        // Popup Search Box
        $(function () {
            $("#popup-search-box").removeClass("toggled");

            $(".dl-search-icon").on("click", function (e) {
                e.stopPropagation();
                $("#popup-search-box").toggleClass("toggled");
                $("#popup-search").focus();
            });

            $("#popup-search-box input").on("click", function (e) {
                e.stopPropagation();
            });

            $("#popup-search-box, body").on("click", function () {
                $("#popup-search-box").removeClass("toggled");
            });
        });

        // Popup Sidebox
        function sideBox() {
            $("body").removeClass("open-sidebar");
            $(document).on("click", ".sidebar-trigger", function (e) {
                e.preventDefault();
                $("body").toggleClass("open-sidebar");
            });
            $(document).on("click", ".sidebar-trigger.close, #sidebar-overlay", function (e) {
                e.preventDefault();
                $("body.open-sidebar").removeClass("open-sidebar");
            });
        }

        sideBox();

        // Venobox Video
        new VenoBox({
            selector: ".video-popup, .img-popup",
            bgcolor: "transparent",
            numeration: true,
            infinigall: true,
            spinner: "plane",
        });

        // Data Background
        $("[data-background").each(function () {
            $(this).css("background-image", "url( " + $(this).attr("data-background") + "  )");
        });

        // Custom Cursor
        $("body").append('<div class="mt-cursor"></div>');
        var cursor = $(".mt-cursor"),
            linksCursor = $("a, .swiper-nav, button, .cursor-effect"),
            crossCursor = $(".cross-cursor");

        $(window).on("mousemove", function (e) {
            cursor.css({
                transform: "translate(" + (e.clientX - 15) + "px," + (e.clientY - 15) + "px)",
                visibility: "inherit",
            });
        });

        /* Odometer */
        $(".odometer").waypoint(
            function () {
                var odo = $(".odometer");
                odo.each(function () {
                    var countNumber = $(this).attr("data-count");
                    $(this).html(countNumber);
                });
            },
            {
                offset: "80%",
                triggerOnce: true,
            }
        );

        // Nice Select Js
        $("select").niceSelect();

        // Award Animation

        let lastActiveImg = $(".award-main-img").attr("src"); // Store the default image

        $(document).on("mouseenter", ".award-list-item", function() {
            let newImg = $(this).data("img"); // Get image from data attribute
            
            // Smooth transition: fade out, change src, fade in
            $(".award-main-img").stop().fadeOut(300, function() {
                $(this).attr("src", newImg).fadeIn(300);
            });

            $(".award-list-item").removeClass("active"); // Remove active class from all
            $(this).addClass("active"); // Add active class to hovered item
            
            lastActiveImg = newImg; // Store last active image
        });

        // Testi Carousel
        var swiperTesti = new Swiper(".testi-carousel", {
            slidesPerView: 2,
            spaceBetween: 24,
            slidesPerGroup: 1,
            loop: true,
            autoplay: true,
            grabcursor: true,
            speed: 600,
            navigation: {
                nextEl: '.testi-top .swiper-prev',
                prevEl: '.testi-top .swiper-next',
            },
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            breakpoints: {
                320: {
                    slidesPerView: 1,
                    slidesPerGroup: 1,
                },
                767: {
                    slidesPerView: 1,
                    slidesPerGroup: 1,
                },
                1024: {
                    slidesPerView: 2,
                    slidesPerGroup: 1,
                },
            },
        });

        // Testi Carousel
        var swiperTesti = new Swiper(".testi-carousel-2", {
            slidesPerView: 1,
            spaceBetween: 24,
            slidesPerGroup: 1,
            loop: true,
            autoplay: true,
            grabcursor: true,
            speed: 600,
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            navigation: {
                nextEl: '.testimonial-section-2 .swiper-prev',
                prevEl: '.testimonial-section-2 .swiper-next',
            },
        });

        // Service Carousel
        var swiperService = new Swiper(".service-carousel", {
            slidesPerView: 3,
            spaceBetween: 24,
            slidesPerGroup: 1,
            grabcursor: true,
            speed: 600,
            navigation: {
                nextEl: '.service-top .swiper-prev',
                prevEl: '.service-top .swiper-next',
            },
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            breakpoints: {
                320: {
                    slidesPerView: 1,
                    slidesPerGroup: 1,
                },
                767: {
                    slidesPerView: 2,
                    slidesPerGroup: 1,
                },
                1024: {
                    slidesPerView: 3,
                    slidesPerGroup: 1,
                },
            },
        });

        // Service Carousel
        var swiperService = new Swiper(".sponsor-carousel", {
            slidesPerView: 6,
            spaceBetween: 24,
            slidesPerGroup: 1,
            grabcursor: true,
            loop: true,
            autoplay: true,
            speed: 600,
            breakpoints: {
                320: {
                    slidesPerView: 2,
                    slidesPerGroup: 1,
                },
                767: {
                    slidesPerView: 4,
                    slidesPerGroup: 1,
                },
                1024: {
                    slidesPerView: 6,
                    slidesPerGroup: 1,
                },
            },
        });

        // Project Carousel
        var swiperProject = new Swiper(".project-carousel", {
            slidesPerView: 1,
            spaceBetween: 24,
            grabcursor: true,
            speed: 600,
            loop: true,
            autoplay: true,
            grabcursor: true,
            navigation: {
                nextEl: '.project-top .swiper-prev',
                prevEl: '.project-top .swiper-next',
            },
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            breakpoints: {
                0: {
                    slidesPerView: 1,
                },
                768: {
                    slidesPerView: 2,
                },
                992: {
                    slidesPerView: 3,
                },
                1200: {
                    slidesPerView: 3,
                }
            },
        });

        // hover reveal start
            const hoverItem = document.querySelectorAll(".service-hover-reveal-item");
            function moveImage(e, hoverItem, index) {
                const item = hoverItem.getBoundingClientRect();
                const x = e.clientX - item.x;
                const y = e.clientY - item.y;
                if (hoverItem.children[index]) {
                    hoverItem.children[index].style.transform = `translate(${x}px, ${y}px)`;
                }
            }
            hoverItem.forEach((item, i) => {
                item.addEventListener("mousemove", (e) => {
                    setInterval(moveImage(e, item, 1), 50);
                });
            });
	    // hover reveal end

        // carouselTicker initail 
        $('.carouselTicker-nav').carouselTicker({
        });
        $(".carouselTicker-start").carouselTicker({
            direction: "next",
        });

        //Running Animated Text
        const scrollers = document.querySelectorAll(".scroller");

        if (!window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
            addAnimation();
        }

        function addAnimation() {
            scrollers.forEach((scroller) => {
                scroller.setAttribute("data-animated", true);

                const scrollerInner = scroller.querySelector(".scroller__inner");
                const scrollerContent = Array.from(scrollerInner.children);

                scrollerContent.forEach((item) => {
                    const duplicatedItem = item.cloneNode(true);
                    duplicatedItem.setAttribute("aria-hidden", true);
                    scrollerInner.appendChild(duplicatedItem);
                });
            });
        }


        // Image Reveal

        gsap.registerPlugin(ScrollTrigger);

        let revealContainers = document.querySelectorAll(".reveal");

        revealContainers.forEach((container) => {
        let image = container.querySelector("img");
        let tl = gsap.timeline({
            scrollTrigger: {
            trigger: container,
            toggleActions: "restart none none reset"
            }
        });

        tl.set(container, { autoAlpha: 1 });
            tl.from(container, 1.5, {
                xPercent: -100,
                ease: Power2.out
            });
            tl.from(image, 1.5, {
                xPercent: 100,
                scale: 1.3,
                delay: -1.5,
                ease: Power2.out
            });
        });

        const images = document.querySelectorAll(".img-reveal");

        const removeOverlay = overlay => {
            let tl = gsap.timeline();

            tl.to(overlay, {
                duration: 1.4,
                ease: "Power2.easeInOut",
                width: "0%"
            });

            return tl;
        };

        const scaleInImage = image => {
            let tl = gsap.timeline();

            tl.from(image, {
                duration: 1.4,
                scale: 1.4,
                ease: "Power2.easeInOut"
            });

            return tl;
        };

        images.forEach(image => {
        
            gsap.set(image, {
                visibility: "visible"
            });
        
            const overlay = image.querySelector('.img-overlay');
            const img = image.querySelector("img");

            const masterTL = gsap.timeline({ paused: true });
            masterTL
            .add(removeOverlay(overlay))
            .add(scaleInImage(img), "-=1.4");
        
        
        let options = {
            threshold: 0
        }

            const io = new IntersectionObserver((entries, options) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        masterTL.play();
                    } else {
                masterTL.progress(0).pause()
            }
                });
            }, options);

            io.observe(image);
        });

        // Scroll Animation

        let typeSplit = new SplitType("[data-text-animation]", {
            types: "lines,words, chars",
            className: "line",
        });
        var text_animations = document.querySelectorAll(
            "[data-text-animation]"
            );
            
            function createScrollTrigger(triggerElement, timeline) {
            // Play tl when scrolled into view (60% from top of screen)
            ScrollTrigger.create({
                trigger: triggerElement,
                start: "top 80%",
                onEnter: () => timeline.play(),
                toggleClass: {targets: triggerElement, className: "active"} 
            });
        }

            text_animations.forEach((animation) => {
            let type = "slide-up",
            duration = 0.75,
            offset = 80,
            stagger = 0.6,
            delay = 0,
            scroll = 1,
            split = "line",
            ease = "power2.out";
        // Set attribute
        if (animation.getAttribute("data-stagger")) {
            stagger = animation.getAttribute("data-stagger");
        }
        if (animation.getAttribute("data-duration")) {
            duration = animation.getAttribute("data-duration");
        }
        if (animation.getAttribute("data-text-animation")) {
            type = animation.getAttribute("data-text-animation");
        }
        if (animation.getAttribute("data-delay")) {
            delay = animation.getAttribute("data-delay");
        }
        if (animation.getAttribute("data-ease")) {
            ease = animation.getAttribute("data-ease");
        }
        if (animation.getAttribute("data-scroll")) {
            scroll = animation.getAttribute("data-scroll");
        }
        if (animation.getAttribute("data-offset")) {
            offset = animation.getAttribute("data-offset");
        }
        if (animation.getAttribute("data-split")) {
            split = animation.getAttribute("data-split");
        }
        if (scroll == 1) {
            if (type == "slide-up") {
            let tl = gsap.timeline({ paused: true });
            tl.from(animation.querySelectorAll(`.${split}`), {
                yPercent: offset,
                duration,
                ease,
                opacity: 0,
                stagger: { amount: stagger },
            });
            createScrollTrigger(animation, tl);
            }
            if (type == "slide-down") {
            let tl = gsap.timeline({ paused: true });
            tl.from(animation.querySelectorAll(`.${split}`), {
                yPercent: -offset,
                duration,
                ease,
                opacity: 0,
                stagger: { amount: stagger },
            });
            createScrollTrigger(animation, tl);
            }
            if (type == "rotate-in") {
            let tl = gsap.timeline({ paused: true });
            tl.set(animation.querySelectorAll(`.${split}`), {
                transformPerspective: 400,
            });
            tl.from(animation.querySelectorAll(`.${split}`), {
                rotationX: -offset,
                duration,
                ease,
                force3D: true,
                opacity: 0,
                transformOrigin: "top center -50",
                stagger: { amount: stagger },
            });
            createScrollTrigger(animation, tl);
            }
            if (type == "slide-from-left") {
            let tl = gsap.timeline({ paused: true });
            tl.from(animation.querySelectorAll(`.${split}`), {
                opacity: 0,
                xPercent: -offset,
                duration,
                opacity: 0,
                ease,
                stagger: { amount: stagger },
            });
            createScrollTrigger(animation, tl);
            }
            if (type == "slide-from-right") {
            let tl = gsap.timeline({ paused: true });
            tl.from(animation.querySelectorAll(`.${split}`), {
                opacity: 0,
                xPercent: offset,
                duration,
                opacity: 0,
                ease,
                stagger: { amount: stagger },
            });
            createScrollTrigger(animation, tl);
            }
            if (type == "fade-in") {
            let tl = gsap.timeline({ paused: true });
            tl.from(animation.querySelectorAll(`.${split}`), {
                opacity: 0,
                duration,
                ease,
                opacity: 0,
                stagger: { amount: stagger },
            });
            createScrollTrigger(animation, tl);
            }
            if (type == "fade-in-right") {
                let tl = gsap.timeline({ paused: true });
                tl.from(animation.querySelectorAll(`.${split}`), {
                    x: 100,
                    autoAlpha: 0,
                    duration,
                    stagger: stagger,
                });
                createScrollTrigger(animation, tl);
            }
            if (type == "fade-in-bottom-line") {
                let tl = gsap.timeline({ paused: true });
                tl.from(animation.querySelectorAll(`.${split}`), {
                    autoAlpha: 0,
                    rotationX: -80,
                    force3D: true,
                    transformOrigin: "top center -50",
                    delay: 0.3,
                    duration,
                    stagger: stagger,
                });
                createScrollTrigger(animation, tl);
            }
            if (type == "fade-in-random") {
            let tl = gsap.timeline({ paused: true });
            tl.from(animation.querySelectorAll(`.${split}`), {
                opacity: 0,
                duration,
                ease,
                opacity: 0,
                stagger: { amount: stagger, from: "random" },
            });
            createScrollTrigger(animation, tl);
            }
            if (type == "scrub") {
            let tl = gsap.timeline({
                scrollTrigger: {
                trigger: animation,
                start: "top 90%",
                end: "top center",
                scrub: true,
                },
            });
            tl.from(animation.querySelectorAll(`.${split}`), {
                opacity: 0.2,
                duration,
                ease,
                stagger: { amount: stagger },
            });
            }

            // Avoid flash of unstyled content
            gsap.set("[data-text-animation]", { opacity: 1 });
        } else {
            if (type == "slide-up") {
            let tl = gsap.timeline({ paused: true });
            tl.from(animation.querySelectorAll(`.${split}`), {
                yPercent: offset,
                duration,
                ease,
                opacity: 0,
            });
            }
            if (type == "slide-down") {
            let tl = gsap.timeline({ paused: true });
            tl.from(animation.querySelectorAll(`.${split}`), {
                yPercent: -offset,
                duration,
                ease,
                opacity: 0,
            });
            }
            if (type == "rotate-in") {
            let tl = gsap.timeline({ paused: true });
            tl.set(animation.querySelectorAll(`.${split}`), {
                transformPerspective: 400,
            });
            tl.from(animation.querySelectorAll(`.${split}`), {
                rotationX: -offset,
                duration,
                ease,
                force3D: true,
                opacity: 0,
                transformOrigin: "top center -50",
            });
            }
            if (type == "slide-from-right") {
            let tl = gsap.timeline({ paused: true });
            tl.from(animation.querySelectorAll(`.${split}`), {
                opacity: 0,
                xPercent: offset,
                duration,
                opacity: 0,
                ease,
            });
            }
            if (type == "fade-in") {
            let tl = gsap.timeline({ paused: true });
            tl.from(animation.querySelectorAll(`.${split}`), {
                opacity: 0,
                duration,
                ease,
                opacity: 0,
            });
            }
            if (type == "fade-in-random") {
            let tl = gsap.timeline({ paused: true });
            tl.from(animation.querySelectorAll(`.${split}`), {
                opacity: 0,
                duration,
                ease,
                opacity: 0,
                stagger: { amount: stagger, from: "random" },
            });
            }
            if (type == "scrub") {
            tl.from(animation.querySelectorAll(`.${split}`), {
                opacity: 0.2,
                duration,
                ease,
            });
            }
        }
        });

        if ($(".fade-wrapper").length > 0) {
            $(".fade-wrapper").each(function () {
                var section = $(this);
                var fadeItems = section.find(".fade-top");
        
                fadeItems.each(function (index, element) {
                var delay = index * 0.10;
        
                gsap.set(element, {
                    opacity: 0,
                    y: 100,
                });
        
                ScrollTrigger.create({
                    trigger: element,
                    start: "top 100%",
                    end: "bottom 20%",
                    scrub: 0.5,
                    onEnter: function () {
                    gsap.to(element, {
                        opacity: 1,
                        y: 0,
                        duration: 1,
                        delay: delay,
                    });
                    },
                    once: true,
                });
                });
            });
        }
        
        window.addEventListener("load", (event) => {
            setTimeout(() => {
                function textAnimationEffect(){
                    let TextAnim = gsap.timeline();
                    let splitText = new SplitType( ".text-animation-effect", { types: 'chars' });
                    if( $('.text-animation-effect .char').length ){
                        TextAnim.from(".text-animation-effect .char", { duration: 1, x: 50, autoAlpha: 0, stagger: 0.1 }, "-=1");
                    }
                }
                textAnimationEffect();
            }, 200);
        });

        // scale animation 
        var scale = document.querySelectorAll(".scale");
        var image = document.querySelectorAll(".scale img");
        scale.forEach((item) => {
            gsap.to(item, {
            scale: 1,
            duration: 1,
            ease: "power1.out",
            scrollTrigger: {
                trigger: item,
                start: 'top bottom',
                end: "bottom top",
                toggleActions: 'play reverse play reverse'
            }
            });
        });
        image.forEach((image) => {
            gsap.set(image, {
            scale: 1.3,
            });
            gsap.to(image, {
            scale: 1,
            duration: 1,
            scrollTrigger: {
                trigger: image,
                start: 'top bottom',
                end: "bottom top",
                toggleActions: 'play reverse play reverse'
            }
            });
        })

        // Page Scroll Percentage
        function scrollTopPercentage() {
            const scrollPercentage = () => {
                const scrollTopPos = document.documentElement.scrollTop;
                const calcHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
                const scrollValue = Math.round((scrollTopPos / calcHeight) * 100);
                const scrollElementWrap = $("#scroll-percentage");

                scrollElementWrap.css("background", `conic-gradient( var(--cp-color-theme-primary) ${scrollValue}%, var(--cp-color-bg-2) ${scrollValue}%)`);
                
                // ScrollProgress
                if ( scrollTopPos > 100 ) {
                    scrollElementWrap.addClass("active");
                } else {
                    scrollElementWrap.removeClass("active");
                }

                if( scrollValue < 96 ) {
                    $("#scroll-percentage-value").text(`${scrollValue}%`);
                } else {
                    $("#scroll-percentage-value").html('<i class="fa-sharp fa-regular fa-arrow-up-long"></i>');
                }
            }
            window.onscroll = scrollPercentage;
            window.onload = scrollPercentage;

            // Back to Top
            function scrollToTop() {
                document.documentElement.scrollTo({
                    top: 0,
                    behavior: "smooth"
                });
            }
            
            $("#scroll-percentage").on("click", scrollToTop);
        }

        scrollTopPercentage();
    });

    document.querySelectorAll(".scroll-btn").forEach((btn, index) => {
        btn.addEventListener("click", () => {
            var sectionTarget = btn.getAttribute("data-target");
            gsap.to(window, {duration: 1, scrollTo:{y:sectionTarget, offsetY:70}});
        });
    });

})(jQuery);
