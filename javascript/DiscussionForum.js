$(document).ready(function() {
    // The slider being synced must be initialized first
    $('#discussion-carousel').flexslider({
        animation: "slide",
        controlNav: false,
        animationLoop: false,
        slideshow: false,
        itemWidth: 125,
        itemMargin: 5,
        asNavFor: '#discussion-slider'
    });

    $('#discussion-slider').flexslider({
        animation: "slide",
        controlNav: false,
        animationLoop: false,
        slideshow: false,
        sync: "#discussion-carousel"
    });
});
