import Qmap from './qmap'
import QmapMarker from './qmap-marker'
import QmapSearchBar from './qmap-search-bar'

const components = [
    Qmap,
    QmapMarker,
    QmapSearchBar,
];

const install = function(Vue, opts = {}){

    components.map(component => {
        Vue.component(component.name, component);
    });

};

export default {
    install,
    Qmap,
    QmapMarker,
    QmapSearchBar,
}