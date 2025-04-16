var slideIndex = 1;
showSlides(slideIndex);

// Set interval for automatic slideshow
var slideInterval = setInterval(function() {
    plusSlides(1);
}, 5000); // Change slide every 5 seconds

function plusSlides(n) {
    clearInterval(slideInterval); // Clear previous interval
    slideInterval = setInterval(function() {
        plusSlides(1);
    }, 5000); // Set new interval after changing slide
    showSlides(slideIndex += n);
}

function currentSlide(n) {
    clearInterval(slideInterval); // Clear previous interval
    slideInterval = setInterval(function() {
        plusSlides(1);
    }, 5000); // Set new interval after changing slide
    showSlides(slideIndex = n);
}

function showSlides(n) {
    var i;
    var slides = document.querySelectorAll('.slides img');
    var dots = document.querySelectorAll('.dot');
    if (n > slides.length) {slideIndex = 1}
    if (n < 1) {slideIndex = slides.length}
    for (i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";
    }
    for (i = 0; i < dots.length; i++) {
        dots[i].className = dots[i].className.replace(" active", "");
    }
    slides[slideIndex-1].style.display = "block";
    dots[slideIndex-1].className += " active";
}

