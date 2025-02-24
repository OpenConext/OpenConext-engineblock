'use strict';

/**
 * Automatically click the input button to redirect the user to the SP
 */

window.handleState = function handleState() {
  if (document.readyState === 'interactive' || document.readyState === "complete") {
    document.forms[0].submit();
  }
};

document.onreadystatechange = window.handleState;
