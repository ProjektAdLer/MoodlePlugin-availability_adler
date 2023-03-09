YUI.add('moodle-availability_adler-form', function (Y, NAME) {

/**
 * JavaScript for form editing group conditions.
 *
 * @module moodle-availability_adler-form
 */
M.availability_adler = M.availability_adler || {}; // eslint-disable-line camelcase

/**
 * @class M.availability_adler.form
 * @extends M.core_availability.plugin
 */
M.availability_adler.form = Y.Object(M.core_availability.plugin);

M.availability_adler.form.getNode = function(json) {
    // Changing value in web form is not supported.
    M.availability_adler.condition = json.condition;

    return Y.Node.create(
        '<span class="form-inline">'
        + M.util.get_string('node_adler_rule', 'availability_adler', json.condition)
        + '</span>');
};

/** This function is called when the form is submitted and updates "value" with the selection the user did inside the node.
 * The value attribute does not contain the values of the form (in this case the condition attribute).
 * This function has to be implemented.
 *
 * @method fillValue
 * @param {Object} value
 * @param {Node} node
 * @return {Object} value
 */
M.availability_adler.form.fillValue = function(value, node) {
    // Changing value in web form is not supported.
    value.condition = M.availability_adler.condition;
    return value;
};


}, '@VERSION@', {"requires": ["base", "node", "event", "moodle-core_availability-form"]});
