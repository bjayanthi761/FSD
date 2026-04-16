package com.campus.eventmanagement.controller;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.ui.Model;
import org.springframework.web.bind.annotation.*;

import com.campus.eventmanagement.model.Event;
import com.campus.eventmanagement.service.EventService;

@Controller
public class EventController {

    @Autowired
    private EventService service;

    @GetMapping("/")
    public String home(Model model) {
        model.addAttribute("events", service.getAllEvents());
        return "index";
    }

    @GetMapping("/new")
    public String addForm(Model model) {
        model.addAttribute("event", new Event());
        return "form";
    }

    @PostMapping("/save")
    public String save(Event event) {
        service.save(event);
        return "redirect:/";
    }

    @GetMapping("/delete/{id}")
    public String delete(@PathVariable Long id) {
        service.delete(id);
        return "redirect:/";
    }
}
