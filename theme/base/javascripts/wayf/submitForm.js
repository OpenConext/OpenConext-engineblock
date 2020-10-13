import {addSelectedIdp} from './deleteDisable/addSelectedIdp';
import {rememberChoice} from './rememberChoice';
import {getData} from '../utility/getData';

/**
 * Submit the form for the selected idp.
 * This ensures you select the idp to log in.
 *
 * Also checks for the remember choice feature and handles it accordingly.
 *
 * @param e
 * @param previouslySelectedIdps
 */
export const submitForm = (e, previouslySelectedIdps) => {
  let element = e.target;

  if (e.target.className === 'idp__deleteDisable' || e.target.className === 'idp__delete' || e.target.className === 'idp__disabled') {
    return;
  }

  if (e.target.tagName !== 'ARTICLE') {
    element = e.target.closest('.wayf__idp');
  }

  rememberChoice(getData(element, 'entityid'));

  addSelectedIdp(previouslySelectedIdps, element);
  element.querySelector('.idp__form').submit();
};
