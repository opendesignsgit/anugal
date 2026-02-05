/**
 * Unit tests for ROI Calculator
 * Run these tests to verify calculation accuracy
 */

// Test helper function
function assertEquals(actual, expected, tolerance = 0.01) {
    const diff = Math.abs(actual - expected);
    if (diff > tolerance) {
        throw new Error(`Expected ${expected}, got ${actual} (diff: ${diff})`);
    }
}

function runTests() {
    console.log('Running ROI Calculator Tests...\n');
    
    const calculator = new ROICalculator();
    let passed = 0;
    let failed = 0;
    
    // Test 1: Example from algorithm document
    console.log('Test 1: Example calculation from algorithm document');
    try {
        const result = calculator.calculate({
            region: 'US',
            employee_count: 420,
            connected_apps: 5,
            am_percent: 10,
            cli_percent: 15,
            review_cycles: 2,
            days_per_review: 7,
            daily_tickets: 10
        });
        
        // Verify subscription cost
        assertEquals(result.subscription_cost.eur, 17703, 10);
        console.log('✓ Subscription cost matches expected value');
        
        // Verify implementation cost
        assertEquals(result.implementation_cost.eur, 25000, 1);
        console.log('✓ Implementation cost matches expected value');
        
        // Verify hours saved (approximately)
        assertEquals(result.hours_saved_annual, 854, 10);
        console.log('✓ Hours saved matches expected value');
        
        passed += 3;
    } catch (e) {
        console.error('✗ Test failed:', e.message);
        failed++;
    }
    
    // Test 2: Small company (≤500 employees)
    console.log('\nTest 2: Small company pricing');
    try {
        const result = calculator.calculate({
            region: 'EU',
            employee_count: 100,
            connected_apps: 2,
            am_percent: 10,
            cli_percent: 0,
            review_cycles: 2,
            days_per_review: 7,
            daily_tickets: ''
        });
        
        // Verify subscription uses ≤500 pricing
        // 100 employees * €4 = €400/month = €4,800/year
        assertEquals(result.subscription_cost.eur, 4800, 1);
        console.log('✓ Small company pricing correct');
        
        // Verify implementation cost
        assertEquals(result.implementation_cost.eur, 17500, 1);
        console.log('✓ Implementation cost correct');
        
        passed += 2;
    } catch (e) {
        console.error('✗ Test failed:', e.message);
        failed++;
    }
    
    // Test 3: Large company (>500 employees)
    console.log('\nTest 3: Large company pricing');
    try {
        const result = calculator.calculate({
            region: 'EU',
            employee_count: 1000,
            connected_apps: 10,
            am_percent: 5,
            cli_percent: 10,
            review_cycles: 4,
            days_per_review: 5,
            daily_tickets: 25
        });
        
        // Verify subscription uses >500 pricing
        // AM: 50 * €4 = €200
        // ID: 850 * €2 = €1,700
        // CLI: 100 * €0.75 = €75
        // Total: €1,975/month = €23,700/year
        assertEquals(result.subscription_cost.eur, 23700, 1);
        console.log('✓ Large company pricing correct');
        
        // Verify implementation cost
        assertEquals(result.implementation_cost.eur, 37500, 1);
        console.log('✓ Implementation cost correct');
        
        passed += 2;
    } catch (e) {
        console.error('✗ Test failed:', e.message);
        failed++;
    }
    
    // Test 4: Input validation
    console.log('\nTest 4: Input validation');
    try {
        // Should throw error for invalid employee count
        try {
            calculator.calculate({
                region: 'US',
                employee_count: 0,
                connected_apps: 5
            });
            throw new Error('Should have thrown validation error');
        } catch (e) {
            if (e.message.includes('at least 1')) {
                console.log('✓ Validates minimum employee count');
                passed++;
            } else {
                throw e;
            }
        }
        
        // Should throw error for percentages > 100%
        try {
            calculator.calculate({
                region: 'US',
                employee_count: 100,
                connected_apps: 5,
                am_percent: 60,
                cli_percent: 60
            });
            throw new Error('Should have thrown validation error');
        } catch (e) {
            if (e.message.includes('cannot exceed 100')) {
                console.log('✓ Validates percentage sum');
                passed++;
            } else {
                throw e;
            }
        }
        
    } catch (e) {
        console.error('✗ Test failed:', e.message);
        failed++;
    }
    
    // Test 5: Default values
    console.log('\nTest 5: Default values');
    try {
        const result = calculator.calculate({
            region: 'EU',
            employee_count: 200,
            connected_apps: 3,
            am_percent: '',
            cli_percent: '',
            review_cycles: '',
            days_per_review: '',
            daily_tickets: ''
        });
        
        // Verify defaults were applied
        assertEquals(result.raw.inputs.am_percent, 0.10, 0.001);
        console.log('✓ Default AM percent applied (10%)');
        
        assertEquals(result.raw.inputs.cli_percent, 0.00, 0.001);
        console.log('✓ Default CLI percent applied (0%)');
        
        assertEquals(result.raw.inputs.review_cycles_per_year, 2, 0);
        console.log('✓ Default review cycles applied (2)');
        
        assertEquals(result.raw.inputs.days_per_review, 7, 0);
        console.log('✓ Default days per review applied (7)');
        
        passed += 4;
    } catch (e) {
        console.error('✗ Test failed:', e.message);
        failed++;
    }
    
    // Test 6: Currency display
    console.log('\nTest 6: Currency display');
    try {
        const resultUS = calculator.calculate({
            region: 'US',
            employee_count: 100,
            connected_apps: 2
        });
        
        if (resultUS.operational_efficiency.display === 'usd') {
            console.log('✓ US region shows USD');
            passed++;
        }
        
        const resultEU = calculator.calculate({
            region: 'EU',
            employee_count: 100,
            connected_apps: 2
        });
        
        if (resultEU.operational_efficiency.display === 'eur') {
            console.log('✓ EU region shows EUR');
            passed++;
        }
        
    } catch (e) {
        console.error('✗ Test failed:', e.message);
        failed++;
    }
    
    // Test 7: Auto-calculated daily tickets
    console.log('\nTest 7: Auto-calculated daily tickets');
    try {
        const result = calculator.calculate({
            region: 'EU',
            employee_count: 250,
            connected_apps: 3,
            daily_tickets: ''
        });
        
        // For 250 employees: ceil(250/100) * 2 = 3 * 2 = 6
        const expectedTickets = 6;
        assertEquals(result.raw.inputs.daily_access_tickets, expectedTickets, 0);
        console.log('✓ Daily tickets auto-calculated correctly (250 employees)');
        
        // Edge case: exactly 100 employees
        const result100 = calculator.calculate({
            region: 'EU',
            employee_count: 100,
            connected_apps: 2,
            daily_tickets: ''
        });
        assertEquals(result100.raw.inputs.daily_access_tickets, 2, 0);
        console.log('✓ Daily tickets correct for 100 employees (boundary)');
        
        // Edge case: 1 employee
        const result1 = calculator.calculate({
            region: 'EU',
            employee_count: 1,
            connected_apps: 1,
            daily_tickets: ''
        });
        assertEquals(result1.raw.inputs.daily_access_tickets, 2, 0);
        console.log('✓ Daily tickets correct for 1 employee (minimum)');
        
        // Edge case: large company
        const resultLarge = calculator.calculate({
            region: 'EU',
            employee_count: 10000,
            connected_apps: 10,
            daily_tickets: ''
        });
        assertEquals(resultLarge.raw.inputs.daily_access_tickets, 200, 0);
        console.log('✓ Daily tickets correct for 10,000 employees (large)');
        
        passed += 4;
    } catch (e) {
        console.error('✗ Test failed:', e.message);
        failed++;
    }
    
    // Summary
    console.log('\n' + '='.repeat(50));
    console.log(`Tests completed: ${passed} passed, ${failed} failed`);
    console.log('='.repeat(50));
    
    return failed === 0;
}

// Run tests if in Node.js environment
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { runTests };
    
    // Auto-run if executed directly
    if (require.main === module) {
        global.ROICalculator = require('./calculator.js');
        const success = runTests();
        process.exit(success ? 0 : 1);
    }
}
