import React, {Component} from 'react';
import {Route} from 'react-router-dom';
import {Layout} from 'element-react';

import routes from '../route';


class Content extends Component {

    render(){
        return (
            <Layout.Col>
                {
                    routes.map(function (r) {
                        return <Route key={r.path} path={r.path} component={r.component}/>
                    })
                }
            </Layout.Col>
        );
    }
}

export default Content;