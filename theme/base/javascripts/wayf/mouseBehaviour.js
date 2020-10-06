import {submitForm} from './submitForm';

export const mouseBehaviour = () => {
// allow chosing an idp to login
  document
    .querySelector('.wayf__idp')
    .addEventListener('click', (e) => {
      submitForm(e);
    });
};
