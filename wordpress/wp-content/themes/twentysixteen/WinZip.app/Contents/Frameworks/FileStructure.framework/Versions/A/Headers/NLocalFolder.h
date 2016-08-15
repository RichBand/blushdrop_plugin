//
//  NLocalFolder.h
//  FileStructure
//
//  Created by glority on 13-12-29.
//  Copyright (c) 2013年 glority. All rights reserved.
//

#ifndef __FileStructure__NLocalFolder__
#define __FileStructure__NLocalFolder__

#include <iostream>

#include "NFolder.h"
#include "FolderUploadToCloudTask.h"

class NLocalFolder : public NFolder {
public:
    NLocalFolder(std::string path);
    
    FolderUploadToCloudTask* UploadTo(std::shared_ptr<NCloudFolder> target, ProgressChanged onProgressChanged, TaskCompleted onTaskCompleted, TaskCompleted onSubTaskCompleted, TaskFailed onTaskFailed);
    enum FileType FileType() { return LocalFolder; };

    void LoadContent(OnLoadContentCompletion onLoadContentCompletion, bool refresh);
    void LoadChild(std::string name, OnLoadChildCompletion onLoadChildCompletion);
    void CreateSubFolder(std::string name, OnCreateSubFolderCompletion onCreateSubFolderCompletion);
    void Delete(OnItemDeleted onItemDeleted);
    void Rename(std::string newName, OnItemRenamed onItemRenamed);
    
    std::shared_ptr<NLocalFolder> get_shared_ptr();
    
};

#endif /* defined(__FileStructure__NLocalFolder__) */
