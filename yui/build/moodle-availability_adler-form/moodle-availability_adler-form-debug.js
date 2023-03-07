YUI.add('moodle-availability_adler-form', function (Y, NAME) {

/**
 * JavaScript for form editing group conditions.
 *
 * @module moodle-availability_adler-form
 */
M.availability_adler = M.availability_adler || {};

/**
 * @class M.availability_adler.form
 * @extends M.core_availability.plugin
 */
M.availability_adler.form = Y.Object(M.core_availability.plugin);

M.availability_adler.form.getNode = function(json) {
    // TODO: use get_string
    var html = '<p>AdLer rule: ' + json.condition + '</p>';
    var node = Y.Node.create('<span>' + html + '</span>');

    return node;
};

/** For my understanding this function is called when the form is submitted
 * and updates "value" with the selection the user did inside the node.
 * This functin has to be implemented.
 *
 * @method fillValue
 * @param {Object} value
 * @param {Node} node
 * @return {Object} value
 */
M.availability_adler.form.fillValue = function(value, node) {
    return value;
};


}, '@VERSION@', {"requires": ["base", "node", "event", "moodle-core_availability-form"]});
