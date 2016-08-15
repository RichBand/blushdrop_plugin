//
//  TaskManager.h
//  WinZip
//
//  Created by WinZip on 14-1-13.
//  Copyright (c) 2014年 Winzip. All rights reserved.
//

#ifndef __WinZip__TaskManager__
#define __WinZip__TaskManager__

#include "Task.h"

#include <iostream>
#include <list>

class TaskGroup : public Task {
public:
    TaskGroup(ProgressChanged onProgressChanged, TaskCompleted onTaskCompleted, TaskCompleted onSubTaskCompleted, TaskFailed onTaskFailed);
    virtual ~TaskGroup();
    
    void AddSubTask(Task*);
    std::list<Task*> sub_tasks() { return _sub_tasks; };
    
    int TotalItemCount();
    long TotalSize();
    long CompletedSize();
    float Progress();

    bool IsAllSubTaskCompleted();
public:
    void PrepareToStart();
    void Cancel();

public:
    void MarkCompleted();
    
protected:
    virtual void _mPrepare(std::function<void()>);
    virtual void _mCancel();
    std::list<Task*> _sub_tasks;

    TaskCompleted _on_sub_task_completed;

    
};
#endif /* defined(__WinZip__TaskManager__) */
