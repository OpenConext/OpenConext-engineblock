export const searchFormEventListeners = (searchForm, idpPicker) => {
  searchForm.addEventListener('submit', (event) => {
    event.preventDefault();
    idpPicker.selectIdpUnderFocus();
  });
};
