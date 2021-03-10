// Log errors to the CI terminal
export const terminalLog = (violations) => {
  cy.task(
    'log',
    `${violations.length} accessibility violation${
      violations.length === 1 ? '' : 's'
    } ${violations.length === 1 ? 'was' : 'were'} detected`
  );

  console.log(violations);

  // pluck specific keys to keep the table readable
  const violationData = violations.map(
    ({ id, impact, description, nodes }) => ({
      id,
      impact,
      description,
      nodes: nodes.length,
      nodeTarget: nodes.length > 0 && nodes[0].any[0] && nodes[0].any[0].relatedNodes[0] ? nodes[0].any[0].relatedNodes[0].target[0] : '',
      nodeHTML: nodes.length > 0 && nodes[0].any[0] && nodes[0].any[0].relatedNodes[0] ? nodes[0].any[0].relatedNodes[0].html : '',
    })
  );

  cy.task('table', violationData);
};
