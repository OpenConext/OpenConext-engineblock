export const focusAndSmoothScroll = (element, scrollOptions = { behaviour: 'smooth', block: 'center', inline: 'center' }) => {
  // focus element but without scrolling to it
  element.focus({preventScroll: true});
  element.scrollIntoView(scrollOptions);
};
