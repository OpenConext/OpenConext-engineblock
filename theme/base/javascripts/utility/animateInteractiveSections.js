import {nodeListToArray} from './nodeListToArray';

/**
 * Animate all elements on the page that can be animated.
 *
 * This overwrites the default styling, which supports no-JS users.
 * Animations are added with an offensive defense strategy (see the mixins.scss file, for the explanation on the @motion mixin).
 */
export const animateInteractiveSections = (selector) => {
  const animatable = document.querySelectorAll(selector);

  nodeListToArray(animatable).forEach(animatableElement => {
    animatableElement.classList.add('animated');
  });
};
