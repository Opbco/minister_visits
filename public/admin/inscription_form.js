/**
 * Inscription Form Handler (jQuery Version)
 * Manages the dynamic relationship between Specialite and OptionSpecialite
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        const $specialiteSelect = $('.specialite-select');
        const $optionSelect = $('.option-specialite-select');
        
        if (!$specialiteSelect.length || !$optionSelect.length) {
            return;
        }

        // Store original options
        // We use .get() to convert the jQuery object to a standard array of DOM elements
        const allOptions = $optionSelect.find('option').get();
        
        // Function to filter options based on selected specialite
        function filterOptions() {
            const selectedSpecialiteId = $specialiteSelect.val();
            
            if (!selectedSpecialiteId) {
                $optionSelect.prop('disabled', true).val('');
                return;
            }

            // Fetch specialite data to check if it has options
            $.getJSON(`/admin/app/inscription/get-options-specialites/${selectedSpecialiteId}`)
                .done(function(data) {

                    if (data.aOptions) {
                        // Specialite has options - enable and filter
                        $optionSelect.prop('disabled', false);
                        
                        // Clear current options and add the default "Sélectionnez une option"
                        $optionSelect.html('<option value="">Sélectionnez une option</option>');
                        
                        // Clear existing options
                        $optionSelect.empty();
                        
                        // Add filtered options
                        for (let option of data.options) {
                            console.log(option);
                            const $newOption = $(`<option value="${option.id}">${option.label}</option>`);
                            $optionSelect.append($newOption);
                        }
                        
                        // Mark as required
                        $optionSelect.prop('required', true);
                        const $formGroup = $optionSelect.closest('.form-group');
                        const $label = $formGroup.find('label');
                        
                        if ($label.length && !$label.hasClass('required')) {
                            $label.addClass('required');
                            $label.append(' <span class="label-required">*</span>');
                        }

                    } else {
                        // Specialite doesn't have options - disable
                        $optionSelect.prop({
                            'disabled': true, 
                            'required': false
                        }).val('');
                        
                        const $formGroup = $optionSelect.closest('.form-group');
                        const $label = $formGroup.find('label');
                        
                        if ($label.length) {
                            $label.removeClass('required');
                            $label.find('.label-required').remove();
                        }
                    }
                })
                .fail(function(jqxhr, textStatus, error) {
                    console.error('Error fetching specialite data:', textStatus, error);
                    $optionSelect.prop('disabled', true);
                });
        }

        // Initial filter on page load
        filterOptions();

        // Filter when specialite changes
        $specialiteSelect.on('change', filterOptions);

        // Form validation
        const $form = $specialiteSelect.closest('form');
        if ($form.length) {
            $form.on('submit', function(e) {
                const selectedSpecialiteId = $specialiteSelect.val();
                
                if (selectedSpecialiteId && 
                    !$optionSelect.prop('disabled') && 
                    $optionSelect.prop('required') && 
                    !$optionSelect.val()) 
                {
                    e.preventDefault();
                    alert('Veuillez sélectionner une option pour cette spécialité.');
                    $optionSelect.focus();
                }
            });
        }
    });
})(jQuery); // Pass jQuery object as $