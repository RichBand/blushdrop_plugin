//
//  FolderUploadToCloudTask.h
//  WinZip
//
//  Created by WinZip on 14-1-22.
//  Copyright (c) 2014年 Winzip. All rights reserved.
//

#ifndef __WinZip__FolderUploadToCloudTask__
#define __WinZip__FolderUploadToCloudTask__

#include <iostream>
#include "TaskGroup.h"

class NCloudFolder;
class NLocalFolder;

class FolderUploadToCloudTask : public TaskGroup {
public:
    FolderUploadToCloudTask(std::shared_ptr<NLocalFolder> source, std::shared_ptr<NCloudFolder> target, ProgressChanged onProgressChanged, TaskCompleted onTaskCompleted, TaskCompleted onSubTaskCompleted, TaskFailed onTaskFailed);
protected:
    void _mPrepare(std::function<void()>);

public:
    std::shared_ptr<NLocalFolder> source() { return _source; };
    std::string OperatingItemName();
    std::shared_ptr<NCloudFolder> target() { return _target; };

private:
    std::shared_ptr<NLocalFolder> _source;
    std::shared_ptr<NCloudFolder> _target;
};

#endif /* defined(__WinZip__FolderUploadToCloudTask__) */
