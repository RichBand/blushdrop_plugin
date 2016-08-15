//
//  NCloudFolder.h
//  FileStructure
//
//  Created by glority on 13-12-29.
//  Copyright (c) 2013年 glority. All rights reserved.
//

#ifndef __FileStructure__NCloudFolder__
#define __FileStructure__NCloudFolder__

#include <iostream>

#include "NFolder.h"
#include "CloudFolderDownloadTask.h"

@protocol CloudService;
@protocol CloudFolder;
@protocol CloudEntry;

class NLocalFolder;

class NCloudFolder : public NFolder {
public:
    NCloudFolder(std::string path, id entry, id service);
    virtual ~NCloudFolder();
    CloudFolderDownloadTask* DownloadTo(std::shared_ptr<NLocalFolder> target, ProgressChanged onProgressChanged, TaskCompleted onTaskCompleted, TaskCompleted onSubTaskCompleted, TaskFailed onTaskFailed);
    enum FileType FileType() { return CloudFolder; };

    virtual void LoadContent(OnLoadContentCompletion onLoadContentCompletion, bool refresh);
    void LoadChild(std::string name, OnLoadChildCompletion onLoadChildCompletion);
    void CreateSubFolder(std::string name, OnCreateSubFolderCompletion onCreateSubFolderCompletion);
    void Delete(OnItemDeleted onItemDeleted);
    void Rename(std::string name, OnItemRenamed onItemRenamed);

    id<CloudService> CloudService() { return _cloud_service; };
    void set_cloud_folder_entry(id<CloudFolder> cloud_folder_entry) { _cloud_folder_entry = cloud_folder_entry; };
    id<CloudFolder> cloud_folder_entry() { return _cloud_folder_entry; };
    
    std::shared_ptr<NCloudFolder> get_shared_ptr();
protected:
    id<CloudFolder> _cloud_folder_entry;
    id<CloudService> _cloud_service;

private:
    std::shared_ptr<NItem> ConvertEntryToChildItem(id<CloudEntry> entry, id<CloudService> service);
};

#endif /* defined(__FileStructure__NCloudFolder__) */
