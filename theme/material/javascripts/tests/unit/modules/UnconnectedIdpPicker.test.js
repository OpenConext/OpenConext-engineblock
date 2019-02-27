import {UnconnectedIdpPicker} from "../../../modules/UnconnectedIdpPicker";

it('Check if the UnconnectedIdpPicker constructor was called', () => {
    const unconnectedIdpPicker = new UnconnectedIdpPicker(null, null, null);
    expect(unconnectedIdpPicker).toBeInstanceOf(UnconnectedIdpPicker);
});
