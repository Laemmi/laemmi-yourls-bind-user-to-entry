/*
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @category  laemmi-yourls-bind-user-to-entry
 * @package   admin.js
 * @author    Michael Lämmlein <laemmi@spacerabbit.de>
 * @copyright ©2016 laemmi
 * @license   http://www.opensource.org/licenses/mit-license.php MIT-License
 * @version   1.0
 * @since     21.01.16
 */

$(function() {

    // Edit button, show form
    $(".button_laemmi_edit_ldapgroup").click(function(e) {
        e.preventDefault();
        var self = $(this);
        if(self.hasClass('disabled')) {
            return false;
        }
        var buttons = self.closest('.actions').find('.button');
        add_loading(buttons);
        $.post(self.attr('href'), function(data) {
            self.closest('tr').after(data.html);
            end_loading(buttons);
        }, 'json');
    });

    // Submit edit form
    $('#main_table').on('click', '.laemmi_edit_ldapgroup_row input[name="save"]', function(e) {
        e.preventDefault();
        var row = $(this).closest('.laemmi_edit_ldapgroup_row');
        var form = row.find('form');

        $.post(ajaxurl, form.serialize(), function(data) {
            switch (data.status) {
                case 'success':
                    $('#edit-' + row.data('id')).fadeOut(200, function(){
                        $('#main_table tbody').trigger("update");
                    });
                    form.trigger('reset');
                    end_disable('#actions-' + row.data('id') + ' .button');
                    break;
            }

            feedback(data.message, data.status);
        }, 'json')
    });

    // Cancel edit
    $('#main_table').on('click', '.laemmi_edit_ldapgroup_row input[name="cancel"]', function(e) {
        e.preventDefault();
        var row = $(this).closest('.laemmi_edit_ldapgroup_row');
        $("#edit-" + row.data('id')).fadeOut(200, function() {
            end_disable('#actions-' + row.data('id') + ' .button');
        });
    });
});