import {attributesSelector, idpDeleteDisabledSelector, idpSelector, nokSectionSelector, previousSelectionFirstIdp, primaryTooltipLabelSelector, remainingIdpSelector, selectedIdpsSelector, tooltipLabelSelector, unconnectedIdpClass} from '../../../../../theme/base/javascripts/selectors';

/***
 * INDEX SELECTORS
 * ***/
export const indexPageHeader = 'main div.main:first-of-type h2';

/***
 * CONSENT SELECTORS
 * ***/
export const attribute6 = `${attributesSelector}:nth-of-type(6)`;
export const labelSelector = 'label';
export const tooltip3Selector = `${tooltipLabelSelector}[for="tooltip3consent_attribute_source_idp"]`;
export const primaryTooltip3Selector = `${primaryTooltipLabelSelector}[for="tooltip3consent_attribute_source_idp"]`;
export const nokSectionTitleSelector = `${nokSectionSelector} h2`;


/***
 * WAYF SELECTORS
 * ***/
export const idpTitle = `${idpSelector} h3`;
export const unconnectedIdpSelector = `.${unconnectedIdpClass}`;
export const weight100Selector = `${remainingIdpSelector}[data-weight="100"]`;
export const weight215Selector = `${remainingIdpSelector}[data-weight="215"]`;
export const weight60Selector = `${remainingIdpSelector}[data-weight="60"]`;
export const weight7Selector = `${remainingIdpSelector}[data-weight="7"]`;
export const weight8Selector = `${remainingIdpSelector}[data-weight="8"]`;
export const weight82Selector = `${remainingIdpSelector}[data-weight="82"]`;
export const selectedIdpDataIndex1 = `${selectedIdpsSelector}[data-index="1"]`;
export const firstSelectedIdpDeleteDisable = `${previousSelectionFirstIdp} ${idpDeleteDisabledSelector}`;
export const firstRemainingIdp = '.wayf__remainingIdps li:first-of-type .wayf__idp';
