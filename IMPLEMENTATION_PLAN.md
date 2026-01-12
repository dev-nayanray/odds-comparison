# Coupon/Betting Implementation - Complete Plan

## Status Overview
- Phase 1 (Backend AJAX): ✅ COMPLETE
- Phase 2 (JavaScript Updates): ⚠️ NEEDS VERIFICATION
- Phase 3 (PHP Functions): ✅ COMPLETE
- Phase 4 (CSS): ✅ COMPLETE
- Phase 5 (Testing): ⏳ PENDING

## Plan for Implementation

### Step 1: Update JavaScript - Ensure Stake Section Visibility
- Modify `updateCouponUI()` to show stake section when coupon has items
- Ensure stake presets update the input and trigger calculations
- Verify calculation updates display correctly

### Step 2: Verify Calculations
- Total odds calculation (multiplier for accumulators)
- Potential win calculation (stake × total odds)
- Place bet button enable/disable based on valid input

### Step 3: Verify Loading States
- Add loading spinner to place bet button during AJAX
- Disable button during request
- Re-enable on response

### Step 4: Toast Notifications
- Ensure toast styles are loaded
- Verify toast shows on bet success/error

### Step 5: Testing Checklist
- [ ] Add bet to coupon → Stake section appears
- [ ] Click stake preset → Input updates + calculations run
- [ ] Manual stake input → Calculations update
- [ ] Click Place Bet → Loading state, then success/error
- [ ] Balance updates after bet placement

## Files to Modify
1. `assets/js/odds-comparison.js` - Update UI logic and calculations

