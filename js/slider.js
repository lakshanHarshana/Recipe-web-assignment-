/**
 * js/slider.js - Interactive Hero Image Slider with Manual & Auto Controls
 */

class HeroSlider {
    constructor(containerId, intervalTime = 5000) {
        this.container = document.getElementById(containerId);
        if (!this.container) return;

        this.slides = this.container.querySelectorAll('.hero-slide');
        this.prevBtn = this.container.querySelector('.slider-prev');
        this.nextBtn = this.container.querySelector('.slider-next');
        this.pauseBtn = this.container.querySelector('.slider-pause');
        this.indicatorsContainer = this.container.querySelector('.slider-indicators');

        this.currentIndex = 0;
        this.intervalTime = intervalTime;
        this.timer = null;
        this.isPlaying = true;

        this.init();
    }

    init() {
        if (this.slides.length === 0) return;

        // Force show initial slide (index 0)
        this.showSlide(0);

        // Build indicators if container exists
        if (this.indicatorsContainer) {
            this.indicatorsContainer.innerHTML = '';
            this.slides.forEach((_, index) => {
                const dot = document.createElement('span');
                dot.className = `slider-dot ${index === 0 ? 'active' : ''}`;
                dot.addEventListener('click', () => this.goToSlide(index));
                this.indicatorsContainer.appendChild(dot);
            });
        }

        // Attach event listeners for controls
        if (this.prevBtn) {
            this.prevBtn.addEventListener('click', () => {
                this.prevSlide();
                this.restartTimer();
            });
        }

        if (this.nextBtn) {
            this.nextBtn.addEventListener('click', () => {
                this.nextSlide();
                this.restartTimer();
            });
        }

        if (this.pauseBtn) {
            this.pauseBtn.addEventListener('click', () => this.togglePlayPause());
        }

        // Pause on mouse hover
        this.container.addEventListener('mouseenter', () => this.stopTimer());
        this.container.addEventListener('mouseleave', () => {
            if (this.isPlaying) this.startTimer();
        });

        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (this.isElementInViewport(this.container)) {
                if (e.key === 'ArrowLeft') this.prevSlide();
                if (e.key === 'ArrowRight') this.nextSlide();
            }
        });

        this.startTimer();
    }

    showSlide(index) {
        this.slides.forEach((slide, i) => {
            if (i === index) {
                slide.style.display = 'block';
                slide.classList.add('animate-fade-in');
            } else {
                slide.style.display = 'none';
                slide.classList.remove('animate-fade-in');
            }
        });

        // Update indicators
        if (this.indicatorsContainer) {
            const dots = this.indicatorsContainer.querySelectorAll('.slider-dot');
            dots.forEach((dot, i) => {
                dot.classList.toggle('active', i === index);
            });
        }

        this.currentIndex = index;
    }

    nextSlide() {
        const newIndex = (this.currentIndex + 1) % this.slides.length;
        this.showSlide(newIndex);
    }

    prevSlide() {
        const newIndex = (this.currentIndex - 1 + this.slides.length) % this.slides.length;
        this.showSlide(newIndex);
    }

    goToSlide(index) {
        this.showSlide(index);
        this.restartTimer();
    }

    startTimer() {
        this.stopTimer();
        this.timer = setInterval(() => this.nextSlide(), this.intervalTime);
    }

    stopTimer() {
        if (this.timer) {
            clearInterval(this.timer);
            this.timer = null;
        }
    }

    restartTimer() {
        if (this.isPlaying) {
            this.startTimer();
        }
    }

    togglePlayPause() {
        this.isPlaying = !this.isPlaying;
        if (this.isPlaying) {
            this.startTimer();
            if (this.pauseBtn) this.pauseBtn.innerHTML = '<i class="bi bi-pause-fill"></i>';
        } else {
            this.stopTimer();
            if (this.pauseBtn) this.pauseBtn.innerHTML = '<i class="bi bi-play-fill"></i>';
        }
    }

    isElementInViewport(el) {
        const rect = el.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight)
        );
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new HeroSlider('heroSliderContainer', 5000);
});
