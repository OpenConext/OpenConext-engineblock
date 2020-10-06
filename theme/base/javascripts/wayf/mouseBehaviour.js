import {submitForm} from './submitForm';

export const mouseBehaviour = () => {
// allow chosing an idp to login
  document
    .querySelector('.wayf__idpList')
    .addEventListener('click', (e) => {
      submitForm(e);
    });
};
