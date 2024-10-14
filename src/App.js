import './App.css';
import React from 'react';
import {useState} from 'react';
import { Button} from '@material-ui/core';
import axios from 'axios';
import qs from 'qs';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';

const ReportingSystem = () => {
  const [chartdata, setChartData] = useState( 
    [
      { name: 'Redeem Points', points: parseInt(frontend_ajax_object.redeem_points) },
      { name: 'Current Points',  points: parseInt(frontend_ajax_object.current_points) },
      { name: 'Overall Points', points: parseInt(frontend_ajax_object.overall_points) },
    ]
  );

  return (
    <div>
      <div className='wps_wpr_user_reports'>
        <table>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Membership Level</th>
            <th>Referred User Count</th>
            <th>Overall Points</th>
          </tr>
          <tr>
            <td>{frontend_ajax_object.name}</td>
            <td>{frontend_ajax_object.email}</td>
            <td>{frontend_ajax_object.membership_name}</td>
            <td>{frontend_ajax_object.referral_count}</td>
            <td>{frontend_ajax_object.overall_points}</td>
          </tr>
        </table>
      </div>

      <ResponsiveContainer width="100%" height={400}>
        <BarChart data={chartdata}>
          <CartesianGrid strokeDasharray="3 3" />
          <XAxis dataKey="name" />
          <YAxis />
          <Tooltip />
          <Legend />
          <Bar dataKey="points" fill="#8884d8" />
        </BarChart>
      </ResponsiveContainer>
    </div>
  );
};

export default ReportingSystem;
