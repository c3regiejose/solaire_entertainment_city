(function (Drupal, once) {

  Drupal.mainBannerSwiper = {

    init(element) {
      // Prevent duplicate initialization.
      if (element.swiper) {
        return;
      }

      console.log(this.buildOptions(element));

      new Swiper(element, this.buildOptions(element));
    },

    destroy(element) {
      if (element.swiper) {
        element.swiper.destroy(true, true);
      }
    },

    buildOptions(slider) {
      const defaultOptions = this.getDefaultOptions(slider);
      const customOptions = this.getCustomOptions(slider);

      return {
        ...defaultOptions,
        ...customOptions,

        navigation: {
          nextEl: slider.querySelector(".swiper-button-next"),
          prevEl: slider.querySelector(".swiper-button-prev"),
          ...(customOptions.navigation || {}),
        },

        breakpoints: {
          ...defaultOptions.breakpoints,
          ...(customOptions.breakpoints || {}),
        },
      };
    },

    getDefaultOptions(slider) {
      const slidesPerView = Number(
        slider.dataset.slidePerView || 3
      );

      return {
        navigation: {
          nextEl: slider.querySelector(".swiper-button-next"),
          prevEl: slider.querySelector(".swiper-button-prev"),
        },

        centeredSlides: true,
        slidesPerView: slidesPerView,
        loop: false,
        spaceBetween: 20,
        speed: 600,
        freeMode: false,
        grabCursor: true,

        breakpoints: {
          550: {
            slidesPerView: 2,
            slidesPerGroup: 1,
            centeredSlides: false,
          },

          768: {
            centeredSlides: false,
            slidesPerView: 2,
            slidesPerGroup: 2,
          },

          1024: {
            centeredSlides: false,
            slidesPerView: slidesPerView,
            slidesPerGroup: slidesPerView,
          },
        },
      };
    },

    getCustomOptions(slider) {
      try {
        return JSON.parse(
          slider.dataset.swiperOptions || "{}"
        );
      }
      catch (error) {
        console.warn(
          "Invalid data-swiper-options JSON:",
          error
        );

        return {};
      }
    },
  };

  Drupal.behaviors.mainSwiper = {
    attach(context) {
      once('slider', '.main-swiper', context).forEach((element) => {
        if (element.classList.contains('featured_mosaic')) {
          const handleResize = Drupal.debounce(() => {
            if (Drupal.breakpoint.isMax(768)) {
              Drupal.mainBannerSwiper.init(element);
            }
            else {
              Drupal.mainBannerSwiper.destroy(element);
            }
          }, 250);

          // Initialize based on the current viewport.
          handleResize();
          window.addEventListener('resize', handleResize);
        }
        else {
          Drupal.mainBannerSwiper.init(element);
        }
      });
    },
  };
})(Drupal, once);
