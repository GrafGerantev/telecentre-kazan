$(function () {

	const slider = $(".main-slider");
	slider.slick({
		dots: false,
		arrows: false,
		vertical: true,
		infinite: false,
		verticalSwiping: true,
		speed: 900,
		swipe: false,
		swipeToSlide: false,
		responsive: [{
			breakpoint: 1022,
			settings: {
				swipe: true,
			}
		}, ]
	});


	slider.on('wheel', (function (e) {
		e.preventDefault();

		if (e.originalEvent.deltaY < 0) {
			$(this).slick('slickPrev');
		} else {
			$(this).slick('slickNext');
		}
	}));

})