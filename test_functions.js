console.log('Testing function accessibility...');

// Test all critical functions
const functionsToTest = [
    'showTab',
    'refreshDashboard',
    'markAllRead',
    'refreshContacts',
    'addNewTestimonial',
    'activateAllTestimonials',
    'deactivateAllTestimonials',
    'refreshTestimonials',
    'refreshDatabase',
    'loadDatabaseData'
];

functionsToTest.forEach(funcName => {
    if (typeof window[funcName] === 'function') {
        console.log(`✓ ${funcName} is accessible`);
    } else {
        console.error(`✗ ${funcName} is NOT accessible`);
    }
});

// Test showTab specifically
if (typeof showTab === 'function') {
    console.log('✓ showTab function can be called directly');
} else {
    console.error('✗ showTab function cannot be called directly');
}
