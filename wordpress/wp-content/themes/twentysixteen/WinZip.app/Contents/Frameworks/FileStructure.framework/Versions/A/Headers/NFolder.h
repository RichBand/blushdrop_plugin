//
//  NFolder.h
//  FF
//
//  Created by glority on 13-12-23.
//  Copyright (c) 2013年 glority. All rights reserved.
//

#ifndef __FF__NFolder__
#define __FF__NFolder__

#include <string>
#include <map>

#include "NItem.h"

typedef std::function<void(std::shared_ptr<NItem>, std::string error)> OnLoadChildCompletion;
typedef std::function<void(std::shared_ptr<NFolder>, long errorCode, std::string message)> OnCreateSubFolderCompletion;

class NFolder : public NItem {
public:
    NFolder(std::string path) : NItem(path) {};

    std::map<std::string, std::shared_ptr<NItem>> children() { return _children; }
    std::shared_ptr<NItem> ChildAtIndex(unsigned int i);

    virtual void LoadChild(std::string name, OnLoadChildCompletion onLoadChildCompletion) = 0;
    virtual void CreateSubFolder(std::string name, OnCreateSubFolderCompletion onCreateSubFolderCompletion) = 0;

    virtual std::shared_ptr<NItem> QueryInnerNode(FileFormat format);

    std::shared_ptr<NFolder> get_shared_ptr();

protected:
    std::map<std::string, std::shared_ptr<NItem>> _children;
};
#endif /* defined(__FF__NFolder__) */
