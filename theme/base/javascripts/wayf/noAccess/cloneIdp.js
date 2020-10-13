export const cloneIdp = (element) => {
  const clone = element.cloneNode(true);
  clone.querySelector('h3').setAttribute('id', 'temp_clone');
  clone.setAttribute('aria-describedby', 'temp_clone');

  return clone;
};
