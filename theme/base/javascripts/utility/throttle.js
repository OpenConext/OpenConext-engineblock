/**
 * Do something with a specific delay.
 *
 * @param action    function    the function to be executed
 * @param delay     number      the delay in microseconds
 * @returns {function(...[*]=)}
 */
export const throttle = (action, delay) => {
  let timer = null;

  return function () {
    clearTimeout(timer);
    timer = setTimeout(() => action.apply(this, arguments), delay);
  };
};
