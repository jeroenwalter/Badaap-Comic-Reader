/*
  This file is part of Badaap Comic Reader.
  
  Copyright (c) 2012 Jeroen Walter
  
  Badaap Comic Reader is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  Badaap Comic Reader is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with Badaap Comic Reader.  If not, see <http://www.gnu.org/licenses/>.
*/  
/*
  Fix that adds the following:
  - call the callbacks if the JSON parsing fails.
*/
Ext.define('Comic.Remotingproviderfix', {
    override: 'Ext.direct.RemotingProvider',
    /**
     * React to the ajax request being completed
     * @private
     */
    onData: function(options, success, response) {
        var me = this,
            i = 0,
            ln, events, event,
            transaction, transactions;

        if (success) {
            events = me.createEvents(response);
            for (ln = events.length; i < ln; ++i) {
                event = events[i];
                transaction = me.getTransaction(event);
                me.fireEvent('data', me, event);
                
                if (event.getCode() == Ext.direct.Manager.exceptions.PARSE)
                {
                  // If JSON parsing fails, the events array will only have 1 entry.
                  // Now callback all transactions.
                  transactions = [].concat(options.transaction);
                  for (ln = transactions.length; i < ln; ++i) {
                      transaction = me.getTransaction(transactions[i]);
                      if (transaction && transaction.getRetryCount() < me.getMaxRetries()) {
                          transaction.retry();
                      } else {
                          var newevent = Ext.clone(event);
                          newevent.transaction = transaction;
                                                        
                          me.fireEvent('data', me, newevent);
                          if (transaction) {
                              me.runCallback(transaction, newevent, false);
                              Ext.direct.Manager.removeTransaction(transaction);
                          }
                      }
                  }
                }
                else
                if (transaction) {
                    me.runCallback(transaction, event, true);
                    Ext.direct.Manager.removeTransaction(transaction);
                }
            }
        } else {
            transactions = [].concat(options.transaction);
            for (ln = transactions.length; i < ln; ++i) {
                transaction = me.getTransaction(transactions[i]);
                if (transaction && transaction.getRetryCount() < me.getMaxRetries()) {
                    transaction.retry();
                } else {
                    event = Ext.create('Ext.direct.ExceptionEvent', {
                        data: null,
                        transaction: transaction,
                        code: Ext.direct.Manager.exceptions.TRANSPORT,
                        message: 'Unable to connect to the server.',
                        xhr: response
                    });

                    me.fireEvent('data', me, event);
                    if (transaction) {
                        me.runCallback(transaction, event, false);
                        Ext.direct.Manager.removeTransaction(transaction);
                    }
                }
            }
        }
    },
});