const titleImages = document.querySelectorAll('.post .image img');

Array.from(titleImages).forEach(image => {
    image.addEventListener('load', () => fitImage(image));

    if (image.complete && image.naturalWidth !== 0)
        fitImage(image);
});
// adapt image cropping with image aspect ratio
function fitImage(image) {
    const aspectRatio = image.naturalWidth / image.naturalHeight;

    // If image is landscape
    if (aspectRatio > 1) {
        image.style.height = '100%';
        image.style.maxWidth = '200%';
    }

    // If image is portrait
    else if (aspectRatio < 1) {
        image.style.minWidth = '100%';
        image.style.maxHeight = '200%';
    }
}
