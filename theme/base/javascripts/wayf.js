// Temporary code to get wayf to function. Only submit by clicking on a IdP functions for now
import {initializePage} from './page';
import {hideIdpsOnLoad} from './wayf/hideIdpsOnLoad';
import {keyboardBehaviour} from './wayf/keyboardBehaviour';
import {mouseBehaviour} from './wayf/mouseBehaviour';

export function initializeWayf() {
  const callbacksAfterLoad = () => {
    // Initialize variables
    const selectedIdps = document.querySelector('.wayf__previousSelection');
    const configuration = JSON.parse(document.getElementById('wayf-configuration').innerHTML);
    const previouslySelectedIdps = configuration.previousSelectionList;

    keyboardBehaviour(previouslySelectedIdps);
    mouseBehaviour(previouslySelectedIdps);
    hideIdpsOnLoad(selectedIdps, previouslySelectedIdps);
  };

  initializePage('main.wayf', callbacksAfterLoad);
}
