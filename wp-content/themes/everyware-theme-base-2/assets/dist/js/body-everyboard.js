(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
"use strict";

$(document).ready(function () {
  /**
   * Device size names used by Everyboard to formulate class names.
   *
   * @type {string[]}
   */
  var everyboardDevices = ['xs', 'sm', 'md', 'lg'];
  /**
   * Supported frameworks and patterns to the classes Everyboard uses to hide/show elements on different devices.
   *
   * If a pattern is specific to a device, it should be captured by the first parenthesis.
   * Else it will be assumed to be a device-independent class.
   *
   * The devices in `deviceMap` correspond to the four devices in `everyboardDevices`.
   */

  var frameworks = [{
    // Bootstrap4
    hidePattern: /^d-([a-z]{2})?-?none$/,
    showPattern: /^d-([a-z]{2})-[a-z]+/,
    deviceMap: ['sm', 'md', 'lg', 'xl']
  }, {
    // Suit
    hidePattern: /^u-([a-z]{2})-hidden$/,
    showPattern: null,
    deviceMap: ['sm', 'md', 'lg', 'xl']
  }, {
    // Legacy
    hidePattern: /^hidden-([a-z]{2})$/,
    showPattern: null,
    deviceMap: ['xs', 'sm', 'md', 'lg']
  }];
  /**
   * @type {boolean} Setting TRUE optimizes so that only the patterns of the first detected framework will be tried.
   */

  var onlyOneFrameworkCanBeActive = true;
  /**
   * @type {number|null}
   */

  var detectedFrameworkIndex = null;
  /**
   * Add CSS classes to make it clear which elements in a collection that will be
   * the first and last VISIBLE ones, when viewed on a certain device.
   *
   * Classes are only added when something in $collection has been hidden, i.e.
   * when :first-child and :last-child will not be enough to select the elements.
   *
   * @param {jQuery} $collection
   */

  var classifyFirstAndLastVisibleElement = function classifyFirstAndLastVisibleElement($collection) {
    if (detectedFrameworkIndex !== null) {
      classifyUsingFramework($collection, frameworks[detectedFrameworkIndex]);
      return;
    }

    for (var i = 0; i < frameworks.length; i++) {
      var hasMatches = classifyUsingFramework($collection, frameworks[i]);

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


  var getFirstVisibleClass = function getFirstVisibleClass(ebDevice) {
    return 'first-' + ebDevice;
  };
  /**
   * @param {string} ebDevice
   *
   * @returns {string}
   */


  var getLastVisibleClass = function getLastVisibleClass(ebDevice) {
    return 'last-' + ebDevice;
  };
  /**
   * @param {jQuery} $collection
   * @param {object} framework
   *
   * @returns {boolean}  TRUE if found any matches on this framework, else FALSE
   */


  var classifyUsingFramework = function classifyUsingFramework($collection, framework) {
    var foundMatchInFramework = false;

    for (var i = 0; i < framework.deviceMap.length; i++) {
      var ebDevice = everyboardDevices[i];
      var lastVisibleIndex = null;

      for (var j = 0; j < $collection.length; j++) {
        var $element = $collection.eq(j);

        if (frameworkHidesElementOnDevice(framework, $element, i)) {
          continue;
        } // This element is visible on this device!


        var firstVisibleElement = lastVisibleIndex === null;
        var firstElementAnyway = j === 0;
        lastVisibleIndex = j;

        if (firstVisibleElement && !firstElementAnyway) {
          $element.addClass(getFirstVisibleClass(ebDevice));
        }
      }

      if (lastVisibleIndex !== null) {
        var lastElementAnyway = lastVisibleIndex === $collection.length - 1;

        if (!lastElementAnyway) {
          $collection.eq(lastVisibleIndex).addClass(getLastVisibleClass(ebDevice));
        }

        foundMatchInFramework = true;
      }
    }

    return foundMatchInFramework;
  };
  /**
   * @param {object} framework
   * @param {jQuery} $element
   * @param {number} deviceIndex
   *
   * @returns {boolean}
   */


  var frameworkHidesElementOnDevice = function frameworkHidesElementOnDevice(framework, $element, deviceIndex) {
    var isHidden = false;
    var classes = $element.attr('class').split(' '); // From smallest device to largest...

    for (var i = 0; i <= deviceIndex; i++) {
      // Check each class in the order they are mentioned...
      for (var j = 0; j < classes.length; j++) {
        var hideMatch = framework.hidePattern === null ? null : framework.hidePattern.exec(classes[j]);
        var showMatch = framework.showPattern === null ? null : framework.showPattern.exec(classes[j]);

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


  var matchAppliesToDevice = function matchAppliesToDevice(match, deviceIndex, deviceList) {
    if (match === null) {
      return false;
    }

    var specificToDevice = typeof match[1] === "string" ? match[1] : false;
    var isSmallestDevice = deviceIndex === 0;
    return specificToDevice === deviceList[deviceIndex] || specificToDevice === false && isSmallestDevice;
  }; // Classify all rows, columns and items in Everyboards.


  $('.every_board').each(function () {
    var $rows = $(this).children();
    classifyFirstAndLastVisibleElement($rows);
    $rows.each(function () {
      var $cols = $(this).children();
      classifyFirstAndLastVisibleElement($cols);
      $cols.each(function () {
        var $items = $(this).children();
        classifyFirstAndLastVisibleElement($items);
      });
    });
  });
});

},{}]},{},[1])

//# sourceMappingURL=body-everyboard.js.map
