export const interactionListeners = (keyboardListener, mouseListener) => {
  document.addEventListener('keyup', event => keyboardListener.handle(event.keyCode));
  document.addEventListener('mousemove', event => mouseListener.handle(event.target));
};
