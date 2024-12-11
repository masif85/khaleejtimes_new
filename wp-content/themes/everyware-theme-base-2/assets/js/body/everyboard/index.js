"use strict";

$(document).ready(function () {

    /**
     * Device size names used by Everyboard to formulate class names.
     *
     * @type {string[]}
     */
    const everyboardDevices = ['xs', 'sm', 'md', 'lg'];

    /**
     * Supported frameworks and patterns to the classes Everyboard uses to hide/show elements on different devices.
     *
     * If a pattern is specific to a device, it should be captured by the first parenthesis.
     * Else it will be assumed to be a device-independent class.
     *
     * The devices in `deviceMap` correspond to the four devices in `everyboardDevices`.
     */
    const frameworks = [
        { // Bootstrap4
            hidePattern: /^d-([a-z]{2})?-?none$/,
            showPattern: /^d-([a-z]{2})-[a-z]+/,
            deviceMap: ['sm', 'md', 'lg', 'xl']
        },
        { // Suit
            hidePattern: /^u-([a-z]{2})-hidden$/,
            showPattern: null,
            deviceMap: ['sm', 'md', 'lg', 'xl']
        },
        { // Legacy
            hidePattern: /^hidden-([a-z]{2})$/,
            showPattern: null,
            deviceMap: ['xs', 'sm', 'md', 'lg']
        }
    ];

    /**
     * @type {boolean} Setting TRUE optimizes so that only the patterns of the first detected framework will be tried.
     */
    const onlyOneFrameworkCanBeActive = true;

    /**
     * @type {number|null}
     */
    let detectedFrameworkIndex = null;

    /**
     * Add CSS classes to make it clear which elements in a collection that will be
     * the first and last VISIBLE ones, when viewed on a certain device.
     *
     * Classes are only added when something in $collection has been hidden, i.e.
     * when :first-child and :last-child will not be enough to select the elements.
     *
     * @param {jQuery} $collection
     */
    const classifyFirstAndLastVisibleElement = function ($collection) {

        if (detectedFrameworkIndex !== null) {
            classifyUsingFramework($collection, frameworks[detectedFrameworkIndex]);

            return;
        }

        for (let i = 0; i < frameworks.length; i++) {
            const hasMatches = classifyUsingFramework($collection, frameworks[i]);
            if (hasMatches && onlyOneFrameworkCanBeActive) {
                detectedFrameworkIndex = i;

                return;
            }
        }
    };

    /**
     * @param {string} ebDevice
     *
     * @returns {string}
     */
    const getFirstVisibleClass = function (ebDevice) {
        return 'first-' + ebDevice;
    };

    /**
     * @param {string} ebDevice
     *
     * @returns {string}
     */
    const getLastVisibleClass = function (ebDevice) {
        return 'last-' + ebDevice;
    };

    /**
     * @param {jQuery} $collection
     * @param {object} framework
     *
     * @returns {boolean}  TRUE if found any matches on this framework, else FALSE
     */
    const classifyUsingFramework = function ($collection, framework) {
        let foundMatchInFramework = false;

        for (let i = 0; i < framework.deviceMap.length; i++) {
            const ebDevice = everyboardDevices[i];

            let lastVisibleIndex = null;
            for (let j = 0; j < $collection.length; j++) {
                const $element = $collection.eq(j);

                if (frameworkHidesElementOnDevice(framework, $element, i)) {
                    continue;
                }

                // This element is visible on this device!
                const firstVisibleElement = (lastVisibleIndex === null);
                const firstElementAnyway = (j === 0);
                lastVisibleIndex = j;

                if (firstVisibleElement && !firstElementAnyway) {
                    $element.addClass(getFirstVisibleClass(ebDevice));
                }
            }

            if (lastVisibleIndex !== null) {
                const lastElementAnyway = (lastVisibleIndex === $collection.length - 1);
                if (!lastElementAnyway) {
                    $collection.eq(lastVisibleIndex).addClass(getLastVisibleClass(ebDevice));
                }
                foundMatchInFramework = true;
            }
        }

        return foundMatchInFramework;
    }

    /**
     * @param {object} framework
     * @param {jQuery} $element
     * @param {number} deviceIndex
     *
     * @returns {boolean}
     */
    const frameworkHidesElementOnDevice = function (framework, $element, deviceIndex) {
        let isHidden = false;
        const classes = $element.attr('class').split(' ');

        // From smallest device to largest...
        for (let i = 0; i <= deviceIndex; i++) {

            // Check each class in the order they are mentioned...
            for (let j = 0; j < classes.length; j++) {

                const hideMatch = (framework.hidePattern === null) ? null : framework.hidePattern.exec(classes[j]);
                const showMatch = (framework.showPattern === null) ? null : framework.showPattern.exec(classes[j]);

                if (matchAppliesToDevice(hideMatch, i, framework.deviceMap)) {
                    isHidden = true;
                } else if (matchAppliesToDevice(showMatch, i, framework.deviceMap)) {
                    isHidden = false;
                }
            }
        }

        return isHidden;
    };

    /**
     * @param {object|null} match       Result from RegExp.prototype.exec()
     * @param {number}      deviceIndex
     * @param {string[]}    deviceList  In order from smallest to largest
     *
     * @returns {boolean}
     */
    const matchAppliesToDevice = function (match, deviceIndex, deviceList) {
        if (match === null) {
            return false;
        }
        const specificToDevice = (typeof match[1] === "string") ? match[1] : false;
        const isSmallestDevice = (deviceIndex === 0);
        return (specificToDevice === deviceList[deviceIndex] || (specificToDevice === false && isSmallestDevice));
    };

    // Classify all rows, columns and items in Everyboards.
    $('.every_board').each(function () {

        const $rows = $(this).children();
        classifyFirstAndLastVisibleElement($rows);

        $rows.each(function () {
            const $cols = $(this).children();
            classifyFirstAndLastVisibleElement($cols);

            $cols.each(function () {
                const $items = $(this).children();
                classifyFirstAndLastVisibleElement($items);
            });

        });

    });

});
