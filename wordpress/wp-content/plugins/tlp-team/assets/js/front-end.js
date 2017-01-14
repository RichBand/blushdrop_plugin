(function ($) {

    $(".tlp-team").each(function () {
        var $isotope = $(this).find('.tlp-team-isotope');
        if ($isotope.length) {
            var isotope = $isotope.imagesLoaded(function () {
                $.when(HeightResize()).done(function () {
                    isotope.isotope({
                        getSortData: {
                            name: '.name',
                            designation: '.designation',
                        },
                        sortAscending: true,
                        itemSelector: '.team-member',
                    }).isotope('layout');
                });
            });
            var $isotopeButtonGroup = $(this).find('.button-group.sort-by-button-group');
            $isotopeButtonGroup.on('click', 'button', function (e) {
                e.preventDefault();
                var sortByValue = $(this).attr('data-sort-by');
                isotope.isotope({sortBy: sortByValue});
                $(this).parent().find('.selected').removeClass('selected');
                $(this).addClass('selected');
            });
        }
    });

    $(window).resize(HeightResize);
    $(window).load(HeightResize);


    function HeightResize() {
        if ($(window).width() > 768) {
            $(document).imagesLoaded(function () {
                $(".tlp-team").each(function () {
                    var tlpMaxH = 0;
                    $(this).children('div').children(".tlp-equal-height").height("auto");
                    $(this).children('div').children(".tlp-equal-height").each(function () {
                        var $thisH = $(this).outerHeight();
                        if ($thisH > tlpMaxH) {
                            tlpMaxH = $thisH;
                        }
                    });
                    $(this).children('div').children(".tlp-equal-height").height(tlpMaxH + "px");
                });


                var tlpMaxH = 0;
                $(".tlp-team-isotope > div > .tlp-equal-height").height("auto");
                $(".tlp-team-isotope > div > .tlp-equal-height").each(function () {
                    var $thisH = $(this).outerHeight();
                    if ($thisH > tlpMaxH) {
                        tlpMaxH = $thisH;
                    }
                });
                $(".tlp-team-isotope > div > .tlp-equal-height").height(tlpMaxH + "px");
            });
        } else {
            $(".tlp-team").children('div').children(".tlp-equal-height").height("auto");
            $(".tlp-team-isotope > div > .tlp-equal-height").height("auto");
        }
    }

})(jQuery);