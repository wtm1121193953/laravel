import React, {Component} from 'react';
import {
    BrowserRouter as Router,
    Route,
    Link
} from 'react-router-dom';
import { Button } from 'element-react';

import Login from './page/Login'
import Home from './Home'

class App extends Component{

    render(){
        return(
        <Router
            basename="/react"
        >
            <Route path="/" render={(options) => {
                // 根据路径动态渲染
                if(options.location.pathname == '/login'){
                    return <Login/>
                }else{
                    return <Home/>
                }
            }}/>
        </Router>
        );
    }
}

export default App;