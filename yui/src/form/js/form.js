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
    // TODO: use get_string
    var html = '<p>AdLer rule: ' + json.condition + '</p>';
    var node = Y.Node.create('<span>' + html + '</span>');

    return node;
};

/** For my understanding this function is called when the form is submitted
 * and updates "value" with the selection the user did inside the node.
 * The value attribute does not cotain the individual values of the form.
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
