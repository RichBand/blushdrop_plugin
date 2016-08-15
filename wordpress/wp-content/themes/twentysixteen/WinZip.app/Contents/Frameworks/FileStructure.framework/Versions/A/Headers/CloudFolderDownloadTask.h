//
//  CloudFolderDownloadTask.h
//  WinZip
//
//  Created by WinZip on 14-1-13.
//  Copyright (c) 2014年 Winzip. All rights reserved.
//

#ifndef __WinZip__CloudFolderDownloadTask__
#define __WinZip__CloudFolderDownloadTask__

#include <iostream>

#include "TaskGroup.h"

class NCloudFolder;
class NLocalFolder;

class CloudFolderDownloadTask : public TaskGroup {
public:
    CloudFolderDownloadTask(std::shared_ptr<NCloudFolder> source, std::shared_ptr<NLocalFolder> target, ProgressChanged onProgressChanged, TaskCompleted onTaskCompleted, TaskCompleted onSubTaskCompleted, TaskFailed onTaskFailed);
protected:
    void _mPrepare(std::function<void()>);

public:
    std::shared_ptr<NCloudFolder> source() { return _source; };
    std::string OperatingItemName();
    std::shared_ptr<NLocalFolder> target() { return _target; };

private:
    std::shared_ptr<NCloudFolder> _source;
    std::shared_ptr<NLocalFolder> _target;
};

#endif /* defined(__WinZip__CloudFolderDownloadTask__) */
