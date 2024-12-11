document.addEventListener("DOMContentLoaded", function() {

    // Selectors that denote a new storytelling section
    let storytellingSelectors = [
        'div.article_image',
        'div.gallery-slider',
        'div.parallax-image',
        'div.pinned-gallery',
        'div.storytelling-break'
    ];

    // Apply parallax effect to single images
    document.querySelectorAll('div.storytelling > div.article_image[data-storytelling="background-scrollmotion"]').forEach(function(el) {
        parallaxImage(el);
    })

    // Transform scrollmotion to pinned gallery items
    document.querySelectorAll('div.storytelling > div.gallery-slider[data-storytelling="scrollmotion"]').forEach(function(el, i) {
        pinnedGallery(
            el,
            el.querySelectorAll('img.gallery_image'),
            el.getAttribute('data-presentation') ?? 'fullwidth'
        );
    })

    // Transform background-scrollmotion to pinned gallery items
    document.querySelectorAll('div.storytelling > div.gallery-slider[data-storytelling="background-scrollmotion"]').forEach(function(el, i) {
        pinnedGallery(
            el,
            el.querySelectorAll('img.gallery_image'),
            el.getAttribute('data-presentation') ?? 'fullwidth'
        );
    })

    // Apply fade effect to gallery items
    document.querySelectorAll('div.storytelling > div.gallery-slider[data-storytelling="reveal"]').forEach(function(el) {
        fadeGallery(
            el,
            el.querySelectorAll('img.gallery_image'),
            el.getAttribute('data-presentation') ?? 'fullwidth'
        );
    })

    // Transform single half width images into pinned gallery items
    document.querySelectorAll('div.storytelling > div.article_image[data-presentation="lefthalf"]').forEach(function(el, i) {
        halfWidthImage(
            el,
            'left'
        );
    })
    document.querySelectorAll('div.storytelling > div.article_image[data-presentation="righthalf"]').forEach(function(el, i) {
        halfWidthImage(
            el,
            'right'
        );
    })

    /*
     * Transform image into a container and append the next elements
     *   as children. Apply parallax styling to container.
     * 
     * @param image DOM element used to get image and siblings from.
     */
    function parallaxImage(image) {
        // Create new container
        let container = document.createElement('div');
        // Grab image source from child
        let imageSrc = image.querySelector('img').getAttribute('src');
        // Set background image style to use with parallax effect
        container.setAttribute('style', `background-image: url(${imageSrc});`);
        container.classList.add('parallax-image');
        // Append elements as children
        while (image.nextElementSibling && !image.nextElementSibling.matches(storytellingSelectors.join(', '))) {
            container.appendChild(image.nextElementSibling);
        }
        // Replace image with container
        image.replaceWith(container);
    }

    /*
     * Transform gallery into a container and append the next numElements
     *   as children. Apply "fade styling" to images.
     * 
     * @param galleryRoot Root element used to get sibling elements.
     * @param images Array of URLs to display as image sources.
     * @param presentation String describing if images should be fullscreen width or not and which side.
     */
    function fadeGallery(galleryRoot, images, presentation) {
        // Create new container
        let container = document.createElement('div');
        container.classList.add('fade-gallery');

        // Use a container for text elements
        let textContainer = document.createElement('div');
        textContainer.classList.add('text-wrapper');
        if (presentation != 'fullwidth') {
            textContainer.classList.add('storytelling-small');
        }
        if (presentation == 'righthalf') {
            textContainer.classList.add('right-half');
        } else if (presentation == 'lefthalf') {
            textContainer.classList.add('left-half');
        }
        // Append X num elements as children
        while (galleryRoot.nextElementSibling && !galleryRoot.nextElementSibling.matches(storytellingSelectors.join(', '))) {
            textContainer.appendChild(galleryRoot.nextElementSibling);
        }

        // Append text container to root container
        container.appendChild(textContainer);

        // Set min height based on number of images
        container.style.minHeight = '100vh';//images.length * 100 + 'vh';

        // Sub-container wrapper for images
        let imgContainerWrapper = document.createElement('div');
        imgContainerWrapper.classList.add('fade-gallery-container-wrapper');
        // Sub-container for images
        let imgContainer = document.createElement('div');
        if (presentation != 'fullwidth') {
            imgContainer.classList.add('storytelling-small');
        }
        if (presentation == 'righthalf') {
            imgContainer.classList.add('right-half');
        } else if (presentation == 'lefthalf') {
            imgContainer.classList.add('left-half');
        }
        imgContainer.classList.add('fade-gallery-container');

        // Add image elements to container
        images.forEach(function(image, i) {
            // Make image visible by default
            image.classList.add('visible');
            image.setAttribute('data-count', i);
            if (presentation != 'fullwidth') {
                image.classList.add('storytelling-small');
            }
            imgContainer.appendChild(image);
        })

        // Append image container to wrapper
        imgContainerWrapper.appendChild(imgContainer);

        // Append image container wrapper to root container
        container.appendChild(imgContainerWrapper);

        // Replace image with container
        galleryRoot.replaceWith(container);

        // Set up events to manage image display as scrolling happens
        let imageEvents = [...images].map((image, i) => {
            return function() {
                let containerStart = container.getBoundingClientRect()['top'] + window.scrollY;
                //let containerEnd = containerStart + container.scrollHeight;
                let splitHeight = container.scrollHeight / images.length
                let currentSplitStart = (splitHeight * i) + containerStart;
                let currentSplitEnd = (splitHeight * (i + 1)) + containerStart;
                //let viewportTop = window.scrollY;
                let viewportMiddle = window.scrollY + (window.innerHeight / 2);
                let viewportBottom = window.scrollY + window.innerHeight;
                
                let visiblePercent = (viewportMiddle - currentSplitStart + (splitHeight / 2)) / ((currentSplitEnd - currentSplitStart) / 2);
                
                if (visiblePercent < 0) {
                    image.style.opacity = 0;
                } else if (visiblePercent > 1) {
                    image.style.opacity = 1;
                } else {
                    image.style.opacity = visiblePercent;
                }
                
            }
        })

        // Performance work
        // Add event listeners when container is on page.
        // Remove event listeners when container isn't on page.
        let observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting === true) {
                    imageEvents.forEach(imageEvent => {
                        document.addEventListener('scroll', imageEvent);
                    })
                } else {
                    imageEvents.forEach(imageEvent => {
                        document.removeEventListener('scroll', imageEvent);
                    })
                }
            })
        });
        
        observer.observe(container);
    }

    /*
     * Transform gallery into a container and append the next numElements
     *   as children. Apply "pinned styling" to container.
     * 
     * @param galleryRoot Root element used to get sibling elements.
     * @param images Array of URLs to display as image sources.
     * @param presentation String describing if images should be fullscreen width or not and which side.
     */
    function pinnedGallery(galleryRoot, images, presentation) {
        // Create new container
        let container = document.createElement('div');
        container.classList.add('pinned-gallery');

        // Use a container for text elements
        let textContainer = document.createElement('div');
        textContainer.classList.add('text-wrapper');
        if (presentation != 'fullwidth') {
            textContainer.classList.add('storytelling-small');
        }
        if (presentation == 'lefthalf') {
            textContainer.classList.add('left-half');
        } else if (presentation == 'righthalf') {
            textContainer.classList.add('right-half');
        }
        // Append X num elements as children
        while (galleryRoot.nextElementSibling && !galleryRoot.nextElementSibling.matches(storytellingSelectors.join(', '))) {
            textContainer.appendChild(galleryRoot.nextElementSibling);
        }

        // Append text container to root container
        container.appendChild(textContainer);

        // Sub-container wrapper for images
        let imgContainerWrapper = document.createElement('div');
        imgContainerWrapper.classList.add('pinned-gallery-container-wrapper');
        imgContainerWrapper.classList.add('storytelling-small');
        // Sub-container for images
        let imgContainer = document.createElement('div');
        if (presentation != 'fullwidth') {
            imgContainer.classList.add('storytelling-small');
        }
        if (presentation == 'lefthalf') {
            imgContainer.classList.add('left-half');
        } else if (presentation == 'righthalf') {
            imgContainer.classList.add('right-half');
        }
        imgContainer.classList.add('pinned-gallery-container');

        // Add image elements to container
        images.forEach(function(image, i) {
            // Make first image visible by default
            if (i == 0) {
                image.classList.add('visible');
            } 
            image.setAttribute('data-count', i);
            if (!presentation) {
                image.classList.add('storytelling-small');
            }
            imgContainer.appendChild(image);
        })

        // Append image container to wrapper
        imgContainerWrapper.appendChild(imgContainer);

        // Append image container wrapper to root container
        container.appendChild(imgContainerWrapper);

        // Replace image with container
        galleryRoot.replaceWith(container);

        // Set up events to manage image display as scrolling happens
        let imageEvents = [...images].map((image, i) => {
            return function() {
                let containerStart = container.getBoundingClientRect()['top'] + window.scrollY;
                //let containerEnd = containerStart + container.scrollHeight;
                let splitHeight = container.scrollHeight / images.length
                let currentSplitStart = (splitHeight * i) + containerStart;
                let currentSplitEnd = (splitHeight * (i + 1)) + containerStart;
                //let viewportTop = window.scrollY;
                let viewportMiddle = window.scrollY + (window.innerHeight / 2);
                //let viewportBottom = window.scrollY + window.innerHeight;
                if (i === 0) {
                    // First image should always show until container has begun.
                    if (!image.classList.contains('visible') && viewportMiddle <= currentSplitEnd) {
                        image.classList.add('visible')
                    }
                    if (image.classList.contains('visible') && viewportMiddle > currentSplitEnd) {
                        image.classList.remove('visible')
                    }
                } else if (i === images.length - 1) {
                    // Last image should always show after container is passed.
                    if (!image.classList.contains('visible') && viewportMiddle > currentSplitStart) {
                        image.classList.add('visible')
                    }
                    if (image.classList.contains('visible') && viewportMiddle < currentSplitStart) {
                        image.classList.remove('visible')
                    }
                } else {
                    if (!image.classList.contains('visible') && (viewportMiddle > currentSplitStart && viewportMiddle <= currentSplitEnd)) {
                        image.classList.add('visible')
                    }
                    if (image.classList.contains('visible') && (viewportMiddle < currentSplitStart || viewportMiddle > currentSplitEnd)) {
                        image.classList.remove('visible')
                    }
                }
            }
        })

        // Performance work
        // Add event listeners when container is on page.
        // Remove event listeners when container isn't on page.
        let observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting === true) {
                    imageEvents.forEach(imageEvent => {
                        document.addEventListener('scroll', imageEvent);
                    })
                } else {
                    imageEvents.forEach(imageEvent => {
                        document.removeEventListener('scroll', imageEvent);
                    })
                }
            })
        });
        
        observer.observe(container);

    }

    /*
     * Limit image to half screen width and float left or right.
     * 
     * @param root Root element used to get sibling elements.
     * @param side String defining which side to attach image to.
     */
    function halfWidthImage(root, side) {
        if (side == 'left') {
            root.classList.add('leftHalf');
            // Add class to all following paragraphs
            let el = root;
            while (el.nextElementSibling && !el.nextElementSibling.matches(storytellingSelectors.join(', '))) {
                el.nextElementSibling.classList.add('left-half-text');
                el = el.nextElementSibling;
            }
        } else {
            root.classList.add('rightHalf');
            // Add class to all following paragraphs
            let el = root;
            while (el.nextElementSibling && !el.nextElementSibling.matches(storytellingSelectors.join(', '))) {
                el.nextElementSibling.classList.add('right-half-text');
                el = el.nextElementSibling;
            }
        }
    }

    /*
     * Transform gallery with captions into a container. Does not append any child elements.
     *
     * @param galleryRoot Element that will be replaced.
     * @param images Array of URLs to display as image sources.
     */
    function gallery(galleryRoot, images) {
        
    }
});