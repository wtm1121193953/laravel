import React, {Component} from 'react';
import {
    BrowserRouter as Router,
    Route,
    Link
} from 'react-router-dom';
import { Button } from 'element-react';

import Login from './page/Login'
import Home from './page/Home'

class App extends Component{

    render(){
        return(
        <Router
            basename="/react"
        >
            <div>
                <Route path="/login" component={Login}/>
                <Route path="/" component={Home}/>
            </div>
        </Router>
        );
    }
}

export default App;