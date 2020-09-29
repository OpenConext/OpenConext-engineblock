import {UnconnectedIdpPicker} from "../../../../base/javascripts/modules/UnconnectedIdpPicker";

it('Check if the UnconnectedIdpPicker constructor was called', () => {
    const unconnectedIdpPicker = new UnconnectedIdpPicker(null, null, null);

    expect(unconnectedIdpPicker).instanceof(UnconnectedIdpPicker);
});
