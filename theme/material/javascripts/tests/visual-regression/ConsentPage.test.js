import ScreenshotTester from './helper/ScreenshotTester';

const pageTests = [
    [
        'consent',
        'https://engine.vm.openconext.org/functional-testing/consent',
    ],
];

describe('Verify consent page', () => {
    const tester = new ScreenshotTester();
    tester.runAll('consent', '.mod-content', pageTests);
});
