/**
 * Anugal ROI Calculator
 * Implements calculation logic from the Anugal ROI Calculator Algorithm
 */

class ROICalculator {
    constructor() {
        // Default constants
        this.DEFAULT_AM_PERCENT = 0.10;
        this.DEFAULT_CLI_PERCENT = 0.00;
        this.DEFAULT_REVIEW_CYCLES = 2;
        this.DEFAULT_DAYS_PER_REVIEW = 7;
        this.HOURS_PER_DAY = 8;
        this.WORK_DAYS_PER_YEAR = 260;
        this.AVG_TICKET_MINUTES = 15;
        this.TICKET_EFFICIENCY_GAIN = 0.30;
        this.REVIEW_EFFICIENCY_GAIN = 0.40;
        this.REVIEW_PARTICIPATION_FACTOR = 0.35;
        this.COST_PER_HOUR_EUR = 50;
        this.TICKETS_PER_100_EMPLOYEES = 2;
        
        // Exchange rates (can be updated dynamically)
        this.FX_RATE_EUR_TO_USD = 1.08;
    }

    /**
     * Main calculation method
     * @param {Object} inputs - User inputs
     * @returns {Object} - Calculation results
     */
    calculate(inputs) {
        // Step 1: Read and validate inputs
        const validatedInputs = this.validateInputs(inputs);
        
        // Step 2: Apply defaults
        const processedInputs = this.applyDefaults(validatedInputs);
        
        // Step 3: Calculate identity counts
        const identityCounts = this.calculateIdentityCounts(processedInputs);
        
        // Step 4: Calculate subscription pricing
        const subscription = this.calculateSubscription(processedInputs, identityCounts);
        
        // Step 5: Calculate costs
        const costs = this.calculateCosts(processedInputs, subscription);
        
        // Step 6: Calculate savings (uses auto-derived daily tickets from processedInputs)
        const savings = this.calculateSavings(processedInputs, identityCounts);
        
        // Step 7: Calculate ROI
        const roi = this.calculateROI(costs, savings);
        
        // Step 8: Format output
        return this.formatOutput(processedInputs, costs, savings, roi);
    }

    /**
     * Validate user inputs
     */
    validateInputs(inputs) {
        const errors = [];
        
        // Required fields
        if (!inputs.region) errors.push('Region is required');
        if (!inputs.employee_count || inputs.employee_count < 1) {
            errors.push('Employee count must be at least 1');
        }
        if (!inputs.connected_apps || inputs.connected_apps < 1) {
            errors.push('Number of applications must be at least 1');
        }
        
        // Percentage validations
        const amPercent = inputs.am_percent !== undefined && inputs.am_percent !== '' 
            ? parseFloat(inputs.am_percent) / 100 
            : null;
        const cliPercent = inputs.cli_percent !== undefined && inputs.cli_percent !== '' 
            ? parseFloat(inputs.cli_percent) / 100 
            : null;
        
        if (amPercent !== null && (amPercent < 0 || amPercent > 1)) {
            errors.push('AM percentage must be between 0 and 100');
        }
        if (cliPercent !== null && (cliPercent < 0 || cliPercent > 1)) {
            errors.push('CLI percentage must be between 0 and 100');
        }
        if (amPercent !== null && cliPercent !== null && (amPercent + cliPercent > 1)) {
            errors.push('Sum of AM and CLI percentages cannot exceed 100%');
        }
        
        // Review cycles validation
        if (inputs.review_cycles && ![1, 2, 4, 12].includes(parseInt(inputs.review_cycles))) {
            errors.push('Review cycles must be 1, 2, 4, or 12');
        }
        
        // Days per review validation
        if (inputs.days_per_review && (inputs.days_per_review < 1 || inputs.days_per_review > 30)) {
            errors.push('Days per review must be between 1 and 30');
        }
        
        if (errors.length > 0) {
            throw new Error(errors.join('; '));
        }
        
        return inputs;
    }

    /**
     * Apply default values to blank inputs
     */
    applyDefaults(inputs) {
        const dailyAccessTickets = inputs.daily_tickets !== undefined && inputs.daily_tickets !== '' 
            ? parseFloat(inputs.daily_tickets) 
            : null;
            
        return {
            region: inputs.region,
            employee_count: parseInt(inputs.employee_count),
            connected_apps: parseInt(inputs.connected_apps),
            am_percent: inputs.am_percent !== undefined && inputs.am_percent !== '' 
                ? parseFloat(inputs.am_percent) / 100 
                : this.DEFAULT_AM_PERCENT,
            cli_percent: inputs.cli_percent !== undefined && inputs.cli_percent !== '' 
                ? parseFloat(inputs.cli_percent) / 100 
                : this.DEFAULT_CLI_PERCENT,
            review_cycles_per_year: inputs.review_cycles 
                ? parseInt(inputs.review_cycles) 
                : this.DEFAULT_REVIEW_CYCLES,
            days_per_review: inputs.days_per_review 
                ? parseInt(inputs.days_per_review) 
                : this.DEFAULT_DAYS_PER_REVIEW,
            daily_access_tickets: dailyAccessTickets !== null 
                ? dailyAccessTickets 
                : Math.ceil(parseInt(inputs.employee_count) / 100) * this.TICKETS_PER_100_EMPLOYEES
        };
    }

    /**
     * Calculate identity counts
     */
    calculateIdentityCounts(inputs) {
        const totalIdentities = inputs.employee_count;
        const cliCount = Math.round(totalIdentities * inputs.cli_percent);
        
        let result = {
            total_identities: totalIdentities,
            cli_count: cliCount
        };
        
        if (inputs.employee_count <= 500) {
            result.non_cli_count = totalIdentities - cliCount;
        } else {
            result.am_count = Math.round(totalIdentities * inputs.am_percent);
            result.id_count = totalIdentities - result.am_count - cliCount;
        }
        
        return result;
    }

    /**
     * Calculate subscription pricing
     */
    calculateSubscription(inputs, identityCounts) {
        let monthlySubscriptionEur;
        
        if (inputs.employee_count <= 500) {
            monthlySubscriptionEur = (identityCounts.non_cli_count * 4.00) + 
                                     (identityCounts.cli_count * 0.75);
        } else {
            monthlySubscriptionEur = (identityCounts.am_count * 4.00) + 
                                     (identityCounts.id_count * 2.00) + 
                                     (identityCounts.cli_count * 0.75);
        }
        
        return {
            monthly_eur: monthlySubscriptionEur,
            annual_eur: monthlySubscriptionEur * 12
        };
    }

    /**
     * Calculate costs
     */
    calculateCosts(inputs, subscription) {
        const implementationCostEur = 12500 + (2500 * inputs.connected_apps);
        const year1CostEur = subscription.annual_eur + implementationCostEur;
        const cost3yEur = implementationCostEur + (subscription.annual_eur * 3);
        
        return {
            implementation_eur: implementationCostEur,
            annual_subscription_eur: subscription.annual_eur,
            year1_eur: year1CostEur,
            cost_3y_eur: cost3yEur
        };
    }

    /**
     * Calculate savings from automation
     */
    calculateSavings(inputs, identityCounts) {
        // Use daily tickets from processed inputs (already derived if needed)
        const dailyTickets = inputs.daily_access_tickets;
        
        // Ticket hours saved
        const ticketHoursBaseline = dailyTickets * this.WORK_DAYS_PER_YEAR * 
                                    (this.AVG_TICKET_MINUTES / 60);
        const ticketHoursSaved = ticketHoursBaseline * this.TICKET_EFFICIENCY_GAIN;
        
        // Review hours saved
        let reviewerPool;
        if (inputs.employee_count <= 500) {
            reviewerPool = Math.round(identityCounts.total_identities * 0.10);
        } else {
            reviewerPool = identityCounts.am_count;
        }
        
        const activeReviewers = reviewerPool * this.REVIEW_PARTICIPATION_FACTOR;
        const reviewHoursBaseline = inputs.review_cycles_per_year * 
                                    inputs.days_per_review * 
                                    this.HOURS_PER_DAY * 
                                    activeReviewers;
        const reviewHoursSaved = reviewHoursBaseline * this.REVIEW_EFFICIENCY_GAIN;
        
        // Total savings
        const hoursSavedAnnual = ticketHoursSaved + reviewHoursSaved;
        const annualSavingsEur = hoursSavedAnnual * this.COST_PER_HOUR_EUR;
        const savings3yEur = annualSavingsEur * 3;
        
        return {
            hours_saved_annual: hoursSavedAnnual,
            annual_eur: annualSavingsEur,
            savings_3y_eur: savings3yEur,
            ticket_hours_saved: ticketHoursSaved,
            review_hours_saved: reviewHoursSaved
        };
    }

    /**
     * Calculate ROI metrics
     */
    calculateROI(costs, savings) {
        const netYear1Eur = savings.annual_eur - costs.year1_eur;
        const roiYear1 = (netYear1Eur / costs.year1_eur) * 100;
        
        const net3yEur = savings.savings_3y_eur - costs.cost_3y_eur;
        const roi3y = (net3yEur / costs.cost_3y_eur) * 100;
        
        return {
            net_year1_eur: netYear1Eur,
            roi_year1_percent: roiYear1,
            net_3y_eur: net3yEur,
            roi_3y_percent: roi3y
        };
    }

    /**
     * Format output with currency conversion
     */
    formatOutput(inputs, costs, savings, roi) {
        const isUS = inputs.region === 'US';
        
        return {
            // Hours saved
            hours_saved_annual: Math.round(savings.hours_saved_annual),
            
            // Operational efficiency (in preferred currency)
            operational_efficiency: {
                eur: savings.annual_eur,
                usd: savings.annual_eur * this.FX_RATE_EUR_TO_USD,
                display: isUS ? 'usd' : 'eur',
                hours: Math.round(savings.hours_saved_annual)
            },
            
            // Subscription cost
            subscription_cost: {
                eur: costs.annual_subscription_eur,
                usd: costs.annual_subscription_eur * this.FX_RATE_EUR_TO_USD,
                display: isUS ? 'usd' : 'eur'
            },
            
            // Implementation cost
            implementation_cost: {
                eur: costs.implementation_eur,
                usd: costs.implementation_eur * this.FX_RATE_EUR_TO_USD,
                display: isUS ? 'usd' : 'eur'
            },
            
            // ROI Year 1
            roi_year1: {
                net_eur: roi.net_year1_eur,
                net_usd: roi.net_year1_eur * this.FX_RATE_EUR_TO_USD,
                percent: roi.roi_year1_percent,
                display: isUS ? 'usd' : 'eur'
            },
            
            // ROI 3 Years
            roi_3year: {
                net_eur: roi.net_3y_eur,
                net_usd: roi.net_3y_eur * this.FX_RATE_EUR_TO_USD,
                percent: roi.roi_3y_percent,
                display: isUS ? 'usd' : 'eur'
            },
            
            // Raw data for report generation
            raw: {
                inputs: inputs,
                costs: costs,
                savings: savings,
                roi: roi
            }
        };
    }

    /**
     * Format currency value for display
     */
    formatCurrency(value, currency = 'USD') {
        const absValue = Math.abs(value);
        const formatted = new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency,
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(absValue);
        
        return value < 0 ? '-' + formatted : formatted;
    }

    /**
     * Format percentage for display
     */
    formatPercent(value) {
        return value.toFixed(1) + '%';
    }

    /**
     * Format hours for display
     */
    formatHours(hours) {
        return new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(hours) + ' hours';
    }
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ROICalculator;
}
