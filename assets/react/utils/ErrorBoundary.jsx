import React, { Component } from 'react'
import PropTypes from 'prop-types'
import Error from '../controllers/Components/Error';

export default class ErrorBoundary extends Component {
  constructor(props){
    super(props);
    this.state = { hasError: false};
  }

  static getDerivedStateFromError(error){
    return { hasError: true };
  }

  componentDidCatch(error, info){

    console.log("An error occurred: ", error, info);

  }

  render() {
    if(this.state.hasError){
        return <Error message="Error while displaying this part. Please reload the page" />
    }

    return this.props.children;
  }
}
