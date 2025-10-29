/**
 * Tests for gdlLiveSiteControlDataHelpers - Testing Actual Production Code
 *
 * @package
 */

import {
	getLiveSiteControlProperty,
	hasLiveSiteControlData,
	isMigratedSite,
} from '../../common/utils/gdlLiveSiteControlDataHelpers';

describe( 'gdlLiveSiteControlDataHelpers - Production Code Tests', () => {
	let originalGdlLiveSiteControlData;
	let consoleSpy;

	beforeEach( () => {
		// Store original value
		originalGdlLiveSiteControlData = global.gdlLiveSiteControlData;

		// Mock console.error to capture any console errors
		consoleSpy = jest.spyOn( console, 'error' ).mockImplementation( () => {} );
	} );

	afterEach( () => {
		// Restore original value
		if ( originalGdlLiveSiteControlData !== undefined ) {
			global.gdlLiveSiteControlData = originalGdlLiveSiteControlData;
		} else {
			delete global.gdlLiveSiteControlData;
		}

		// Restore console.error
		consoleSpy.mockRestore();
	} );

	describe( 'hasLiveSiteControlData()', () => {
		it( 'should return false when gdlLiveSiteControlData is undefined', () => {
			delete global.gdlLiveSiteControlData;

			const result = hasLiveSiteControlData();
			expect( result ).toBe( false );
			expect( consoleSpy ).not.toHaveBeenCalled();
		} );

		it( 'should return true when gdlLiveSiteControlData is defined', () => {
			global.gdlLiveSiteControlData = { isMigratedSite: false };

			const result = hasLiveSiteControlData();
			expect( result ).toBe( true );
			expect( consoleSpy ).not.toHaveBeenCalled();
		} );
	} );

	describe( 'isMigratedSite() - Testing Actual Production Logic', () => {
		it( 'should return false when gdlLiveSiteControlData is undefined (post-publishing scenario)', () => {
			delete global.gdlLiveSiteControlData;

			// This tests the ACTUAL production code that was causing ReferenceError
			const result = isMigratedSite();
			expect( result ).toBe( false );
			expect( consoleSpy ).not.toHaveBeenCalled();
		} );

		it( 'should return false when isMigratedSite property is false', () => {
			global.gdlLiveSiteControlData = {
				isMigratedSite: false,
				settings: { publishState: 'gdl_site_published' },
			};

			const result = isMigratedSite();
			expect( result ).toBe( false );
			expect( consoleSpy ).not.toHaveBeenCalled();
		} );

		it( 'should return true when isMigratedSite property is true', () => {
			global.gdlLiveSiteControlData = {
				isMigratedSite: true,
				settings: { publishState: 'gdl_site_published' },
			};

			const result = isMigratedSite();
			expect( result ).toBe( true );
			expect( consoleSpy ).not.toHaveBeenCalled();
		} );

		it( 'should return false when isMigratedSite property is missing', () => {
			global.gdlLiveSiteControlData = {
				settings: { publishState: 'gdl_site_published' },
			};

			const result = isMigratedSite();
			expect( result ).toBe( false );
			expect( consoleSpy ).not.toHaveBeenCalled();
		} );
	} );

	describe( 'getLiveSiteControlProperty() - Testing Property Access Safety', () => {
		it( 'should return default value when gdlLiveSiteControlData is undefined', () => {
			delete global.gdlLiveSiteControlData;

			const result = getLiveSiteControlProperty( 'isMigratedSite', 'defaultValue' );
			expect( result ).toBe( 'defaultValue' );
			expect( consoleSpy ).not.toHaveBeenCalled();
		} );

		it( 'should return property value when it exists', () => {
			global.gdlLiveSiteControlData = {
				eventName: 'gdl-live-site-control',
				isMigratedSite: true,
			};

			const migratedResult = getLiveSiteControlProperty( 'isMigratedSite' );
			const eventResult = getLiveSiteControlProperty( 'eventName' );

			expect( migratedResult ).toBe( true );
			expect( eventResult ).toBe( 'gdl-live-site-control' );
			expect( consoleSpy ).not.toHaveBeenCalled();
		} );

		it( 'should return default when property does not exist', () => {
			global.gdlLiveSiteControlData = {
				isMigratedSite: true,
			};

			const result = getLiveSiteControlProperty( 'nonExistentProperty', 'fallback' );
			expect( result ).toBe( 'fallback' );
			expect( consoleSpy ).not.toHaveBeenCalled();
		} );
	} );

	describe( 'Production Integration Tests - Simulating Real Usage', () => {
		it( 'should handle the exact scenario from live-site-control/index.js PublishGuideButton', () => {
			// Simulate: site published, gdlLiveSiteControlData no longer localized
			delete global.gdlLiveSiteControlData;

			// This is the exact logic used in production
			const shouldRenderMigrateButton = isMigratedSite();

			// Should not throw ReferenceError and should return false
			expect( shouldRenderMigrateButton ).toBe( false );
			expect( consoleSpy ).not.toHaveBeenCalled();
		} );

		it( 'should handle the exact scenario from publish-guide/index.js domReady callback', () => {
			// Simulate: site published, gdlLiveSiteControlData no longer localized
			delete global.gdlLiveSiteControlData;

			// This is the exact logic used in production domReady
			const shouldRenderMigrateGuide = isMigratedSite();

			// Should not throw ReferenceError and should return false
			expect( shouldRenderMigrateGuide ).toBe( false );
			expect( consoleSpy ).not.toHaveBeenCalled();
		} );

		it( 'should maintain correct behavior when gdlLiveSiteControlData exists (pre-publishing)', () => {
			global.gdlLiveSiteControlData = {
				eventName: 'gdl-live-site-control',
				isMigratedSite: true,
				settings: {
					blogPublic: 'blog_public',
					publishState: 'gdl_site_published',
				},
			};

			// Test both production usage scenarios
			expect( isMigratedSite() ).toBe( true );
			expect( getLiveSiteControlProperty( 'eventName' ) ).toBe( 'gdl-live-site-control' );
			expect( consoleSpy ).not.toHaveBeenCalled();
		} );

		it( 'should validate the EXACT production line replacement for live-site-control', () => {
			// This test validates the exact replacement we need to make
			// Original: if ( typeof gdlLiveSiteControlData !== 'undefined' && gdlLiveSiteControlData.isMigratedSite )
			// Fixed: if ( isMigratedSite() )

			delete global.gdlLiveSiteControlData;

			// The old way would throw ReferenceError, our way should not
			const oldWayResult = ( typeof global.gdlLiveSiteControlData !== 'undefined' && !! global.gdlLiveSiteControlData?.isMigratedSite );
			const newWayResult = isMigratedSite();

			expect( oldWayResult ).toBe( false );
			expect( newWayResult ).toBe( false );
			expect( consoleSpy ).not.toHaveBeenCalled();
		} );
	} );

	describe( 'Regression Prevention Tests', () => {
		it( 'should prevent the original ReferenceError: gdlLiveSiteControlData is not defined', () => {
			delete global.gdlLiveSiteControlData;

			// These calls should never throw ReferenceError
			expect( () => hasLiveSiteControlData() ).not.toThrow();
			expect( () => isMigratedSite() ).not.toThrow();
			expect( () => getLiveSiteControlProperty( 'isMigratedSite' ) ).not.toThrow();
			expect( consoleSpy ).not.toHaveBeenCalled();
		} );

		it( 'should work correctly across multiple calls after variable becomes undefined', () => {
			// Start with variable defined
			global.gdlLiveSiteControlData = { isMigratedSite: true };
			expect( isMigratedSite() ).toBe( true );

			// Simulate publishing - variable becomes undefined
			delete global.gdlLiveSiteControlData;
			expect( isMigratedSite() ).toBe( false );
			expect( hasLiveSiteControlData() ).toBe( false );

			// Multiple calls should continue to work
			expect( isMigratedSite() ).toBe( false );
			expect( isMigratedSite() ).toBe( false );
			expect( consoleSpy ).not.toHaveBeenCalled();
		} );
	} );

	describe( 'Console Error Prevention Tests', () => {
		it( 'should not log console errors when repeatedly accessing undefined variable', () => {
			delete global.gdlLiveSiteControlData;

			// Call functions multiple times to ensure no accumulated errors
			for ( let i = 0; i < 5; i++ ) {
				hasLiveSiteControlData();
				isMigratedSite();
				getLiveSiteControlProperty( 'isMigratedSite' );
				getLiveSiteControlProperty( 'settings' );
			}

			expect( consoleSpy ).not.toHaveBeenCalled();
		} );

		it( 'should not log console errors during state transitions (defined -> undefined)', () => {
			// Start with variable defined
			global.gdlLiveSiteControlData = {
				isMigratedSite: true,
				settings: { publishState: 'gdl_site_published' },
			};

			// Access properties while defined
			expect( isMigratedSite() ).toBe( true );
			expect( getLiveSiteControlProperty( 'settings' ) ).toEqual( { publishState: 'gdl_site_published' } );

			// Simulate site publishing - variable becomes undefined
			delete global.gdlLiveSiteControlData;

			// Access properties after undefined
			expect( isMigratedSite() ).toBe( false );
			expect( getLiveSiteControlProperty( 'settings', {} ) ).toEqual( {} );

			expect( consoleSpy ).not.toHaveBeenCalled();
		} );

		it( 'should not log console errors when accessing non-existent properties', () => {
			global.gdlLiveSiteControlData = { isMigratedSite: true };

			// Access properties that don't exist
			getLiveSiteControlProperty( 'nonExistent1' );
			getLiveSiteControlProperty( 'nonExistent2', 'default' );
			getLiveSiteControlProperty( 'deeply.nested.property' );

			expect( consoleSpy ).not.toHaveBeenCalled();
		} );
	} );
} );
