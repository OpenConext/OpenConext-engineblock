import {toggleVisibility} from '../utility/toggleVisibility';

/**
 * Toggle the visibility of the remaining Idp section
 */
export const toggleRemaining = () => {
    const remainingIdps = document.querySelector('.wayf__remainingIdps');
    toggleVisibility(remainingIdps);
};
