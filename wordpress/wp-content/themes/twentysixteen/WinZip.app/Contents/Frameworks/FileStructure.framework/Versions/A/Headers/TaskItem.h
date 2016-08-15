//
//  Task.h
//  FileStructure
//
//  Created by glority on 12/29/13.
//  Copyright (c) 2013 glority. All rights reserved.
//

#ifndef FileStructure_Task_h
#define FileStructure_Task_h

#include "Task.h"

#include <list>
#include <thread>
#include <mutex>

#include "NItem.h"

class TaskGroup;

class TaskItem : public Task {
public:
    TaskItem(ProgressChanged, TaskCompleted, TaskFailed onTaskFailed);

    int TotalItemCount();
    virtual long TotalSize() = 0;
    long CompletedSize();
    float Progress();

public:
    void PrepareToStart();
    void Execute();
    void Cancel();
    
public:
    void MarkCompleted();
    
protected:
    virtual void _mPrepare(std::function<void()>);
    virtual void _mExecute(std::function<void()>);
    virtual void _mCancel();
    
    float _progress = 0;

};

#endif
